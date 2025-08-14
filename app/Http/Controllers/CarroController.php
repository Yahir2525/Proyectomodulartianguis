<?php

namespace App\Http\Controllers;

use App\Models\Abono;
use App\Models\Carro;
use App\Models\CarroProducto;
use App\Models\Credito;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CarroController extends Controller
{

    public function index()
    {
        $user = Auth::user();

        $carroIndex = $user->hasRole('administrador')
            ? Carro::with(['productos', 'user', 'pedido'])->get()
            : Carro::with(['productos', 'user', 'pedido'])->where('id_user', $user->id_user)->get();

        $reservasGlobales = CarroProducto::select('id_producto')
            ->selectRaw('SUM(cantidad) as total_reservado')
            ->groupBy('id_producto')
            ->pluck('total_reservado', 'id_producto');

        $todosProductos = Producto::all();

        // Agrega esta línea solo si es administrador
        $usuarios = $user->hasRole('administrador') ? User::all() : collect();

        return view('carro.carroIndex', compact('carroIndex', 'reservasGlobales', 'todosProductos', 'usuarios'));
    }

    public function create()
    {
        $usuario = Auth::user();

        $usuarios = $usuario->hasRole('administrador') ? User::all() : collect();

        $pedidos = Pedido::all();
        $productos = Producto::all();

        $reservas = DB::table('carro_productos')
            ->select('id_producto', DB::raw('SUM(cantidad) as reservadas'))
            ->groupBy('id_producto')
            ->pluck('reservadas', 'id_producto');

        foreach ($productos as $producto) {
            $producto->piezas_disponibles = $producto->piezas - ($reservas[$producto->id_producto] ?? 0);
        }

        return view('carro/createCarro', compact('usuario', 'usuarios', 'pedidos', 'productos'));
    }

    public function store(Request $request)
    {
        $userId = $request->input('id_user');
        $productoId = $request->input('id_producto');
        $cantidad = (int) $request->input('cantidad');

        if ($cantidad <= 0) {
            return back()->with('error', 'La cantidad debe ser mayor a 0.');
        }

        $producto = Producto::findOrFail($productoId);

        // Validar que el producto esté activo (no descontinuado)
        if (!$producto->estado_producto) {
            return back()->with('error', 'Este producto está descontinuado y no puede ser agregado.');
        }

        $user = User::findOrFail($userId);

        // Verificar bloqueo para crédito y método pago
        if ($user->estaBloqueadoParaCredito() && $request->input('metodo_pago') === 'credito') {
            return back()->with('error', 'No puedes comprar a crédito porque tienes pagos atrasados sin abonar. Solo contado.');
        }

        // Crear o usar pedido
        if ($request->has('nuevo_pedido')) {
            $pedido = Pedido::create([
                'id_user' => $userId,
                'estado_pedido' => 1,
                'metodo_pago' => 'contado',
            ]);
        } elseif ($request->filled('id_pedido')) {
            $pedido = Pedido::find($request->input('id_pedido'));
            if (!$pedido || $pedido->estado_pedido == 0) {
                return back()->with('error', 'El pedido está cerrado o no existe.');
            }
        } else {
            return back()->with('error', 'Debes seleccionar un pedido o marcar "crear uno nuevo".');
        }

        // Calcular nuevo total con el producto que quiere agregar
        $totalAnterior = $pedido->total_pedido;
        $nuevoTotal = $totalAnterior + ($producto->precio_unitario * $cantidad);

        // Aplicar reglas de comportamiento de pago
        if ($user->pagaSiempreAdelantado() && $nuevoTotal > $totalAnterior && $user->montoPromedio() > 10000) {
            $user->aumentarLimiteCredito();
        } elseif ($user->tienePagosAtrasadosSinAbonar()) {
            if ($request->input('metodo_pago') === 'credito') {
                return back()->with('error', 'No puedes aumentar el total del pedido a crédito porque tienes pagos atrasados sin abonar.');
            }
        }

        // Crear o buscar carro
        $carro = Carro::firstOrCreate(
            ['id_pedido' => $pedido->id_pedido],
            ['id_user' => $userId]
        );

        // Validar piezas disponibles
        $reservadas = CarroProducto::where('id_producto', $productoId)->sum('cantidad');
        $disponibles = max(0, $producto->piezas - $reservadas);

        if ($cantidad > $disponibles) {
            return back()->with('error', "Solo hay $disponibles piezas disponibles.");
        }

        // Revisar si el producto ya está en el carro y sumar cantidades o agregar nuevo
        $productoExistente = $carro->productos()->where('productos.id_producto', $productoId)->first();
        if ($productoExistente) {
            $cantidadActual = $productoExistente->pivot->cantidad;
            $carro->productos()->updateExistingPivot($productoId, ['cantidad' => $cantidadActual + $cantidad]);
        } else {
            $carro->productos()->attach($productoId, ['cantidad' => $cantidad]);
        }

        $carro->load('productos');

        $nuevoTotal = $this->recalcularTotalPedido($carro);

        // Validar créditos y estado del pedido
        if (!$this->validarPedidoConCreditoVencido($pedido)) {
            return back()->with('error', 'No puedes aumentar un pedido asociado a un crédito vencido o cerrado.');
        }

        if ($pedido->estado_pedido == 0 && !$this->validarCreditoAlModificar($pedido, $nuevoTotal)) {
            return back()->with('error', 'No se puede modificar el pedido porque el crédito superaría los $10,000 o el usuario tiene más de 3 créditos activos.');
        }

        if ($pedido->estado_pedido == 0) {
            $pedido->total_pedido = $nuevoTotal;
            $pedido->save();
            $totalOriginal = $pedido->getOriginal('total_pedido');
            $this->actualizarCreditoConDiferencia($pedido, $totalOriginal, $nuevoTotal);
        }

        return redirect()->route('carro.index')->with('success', 'Producto agregado correctamente.');
    }

    public function agregarMultiples(Request $request)
    {
        $userId = $request->input('id_user');
        $idPedido = $request->input('id_pedido');
        $seleccionados = $request->input('productos_seleccionados', []);
        $cantidades = $request->input('cantidades', []);

        $user = User::findOrFail($userId);

        if ($user->estaBloqueadoParaCredito() && $request->input('metodo_pago') === 'credito') {
            return back()->with('error', 'No puedes comprar a crédito porque tienes pagos atrasados sin abonar. Solo contado.');
        }

        if ($idPedido === 'nuevo') {
            $pedido = Pedido::create(['id_user' => $userId, 'estado_pedido' => 1]);
            $idPedido = $pedido->id_pedido;
        } else {
            $pedido = Pedido::find($idPedido);
            if (!$pedido || $pedido->estado_pedido == 0) {
                return back()->with('error', 'No se puede usar un pedido cerrado.');
            }
        }

        if (empty($seleccionados)) {
            return back()->with('error', 'No seleccionaste ningún producto.');
        }

        // Calcular nuevo total sumando productos seleccionados * cantidades
        $nuevoTotal = $pedido->total_pedido;
        foreach ($seleccionados as $idProducto) {
            $producto = Producto::find($idProducto);
            $cantidad = (int) ($cantidades[$idProducto] ?? 0);
            if ($cantidad <= 0) continue;
            $nuevoTotal += $producto->precio_unitario * $cantidad;
        }

        // Aplicar reglas de comportamiento de pago
        if ($user->pagaSiempreAdelantado() && $nuevoTotal > $pedido->total_pedido && $user->montoPromedio() > 1000) {
            $user->aumentarLimiteCredito();
        } elseif ($user->tienePagosAtrasadosSinAbonar()) {
            if ($request->input('metodo_pago') === 'credito') {
                return back()->with('error', 'No puedes aumentar el total del pedido a crédito porque tienes pagos atrasados sin abonar.');
            }
        }


        $carro = Carro::firstOrCreate(
            ['id_pedido' => $pedido->id_pedido],
            ['id_user' => $pedido->id_user]
        );


        // Validar que ninguno de los productos seleccionados esté descontinuado
        foreach ($seleccionados as $idProducto) {
            $producto = Producto::find($idProducto);
            if (!$producto) {
                return back()->with('error', "El producto con ID $idProducto no existe.");
            }
            if (!$producto->estado_producto) {
                return back()->with('error', "El producto \"{$producto->nombre}\" está descontinuado y no puede ser agregado.");
            }
        }

        $totalAnterior = $pedido->total_pedido;

        foreach ($seleccionados as $idProducto) {
            $cantidad = (int) ($cantidades[$idProducto] ?? 0);
            if ($cantidad <= 0) continue;

            $producto = Producto::find($idProducto);
            $reservadas = CarroProducto::where('id_producto', $idProducto)->sum('cantidad');
            $disponibles = max(0, $producto->piezas - $reservadas);

            if ($cantidad > $disponibles) {
                return back()->with('error', "No hay suficientes piezas de {$producto->nombre} (quedan $disponibles).");
            }

            $productoEnCarro = $carro->productos()->where('productos.id_producto', $idProducto)->first();
            if ($productoEnCarro) {
                $cantidadActual = $productoEnCarro->pivot->cantidad;
                $carro->productos()->updateExistingPivot($idProducto, ['cantidad' => $cantidadActual + $cantidad]);
            } else {
                $carro->productos()->attach($idProducto, ['cantidad' => $cantidad]);
            }
        }

        $carro->load('productos');

        $nuevoTotal = $this->recalcularTotalPedido($carro);
        if (!$this->validarPedidoConCreditoVencido($pedido)) {
            return back()->with('error', 'No puedes aumentar un pedido asociado a un crédito vencido o cerrado.');
        }

        if ($pedido->estado_pedido == 0 && !$this->validarCreditoAlModificar($pedido, $nuevoTotal)) {
            return back()->with('error', 'No se puede modificar el pedido porque el crédito superaría los $10,000 o el usuario tiene más de 3 créditos activos.');
        }

        if ($pedido->estado_pedido == 0) {
            $pedido->total_pedido = $nuevoTotal;
            $pedido->save();
            $totalOriginal = $pedido->getOriginal('total_pedido');
            $this->actualizarCreditoConDiferencia($pedido, $totalOriginal, $nuevoTotal);

        }


        return redirect()->route('carro.index')->with('success', 'Productos agregados correctamente.');
    }

    public function eliminarProducto($id_carro, $id_producto)
    {
        $carro = Carro::with('pedido', 'productos')->findOrFail($id_carro);
        $pedido = $carro->pedido;
        $totalAnterior = $pedido->total_pedido;

        $carro->productos()->detach($id_producto);
        $carro->load('productos');

        if ($carro->productos()->count() == 0) {
            $carro->delete();
        }

        $nuevoTotal = $this->recalcularTotalPedido($carro);

        if ($pedido->estado_pedido == 0) {

            if (!$this->validarPedidoConCreditoVencido($pedido)) {
                return back()->with('error', 'No puedes modificar un pedido asociado a un crédito vencido o cerrado.');
            }

            if (!$this->validarCreditoAlModificar($pedido, $nuevoTotal)) {
                return back()->with('error', 'No se puede modificar el pedido porque el crédito superaría los $10,000 o el usuario tiene más de 3 créditos activos.');
            }

            $pedido->total_pedido = $nuevoTotal;
            $pedido->save();

            $totalOriginal = $pedido->getOriginal('total_pedido');
            $this->actualizarCreditoConDiferencia($pedido, $totalOriginal, $nuevoTotal);
        }


        return redirect()->route('carro.index')->with('success', 'Producto eliminado.');
    }

    public function edit($id_carro, $id_producto)
    {
        $carro = Carro::findOrFail($id_carro);
        $productoActual = $carro->productos()->where('productos.id_producto', $id_producto)->firstOrFail();
        $cantidad = $productoActual->pivot->cantidad;

        $usuario = $carro->user;

        // Filtrar productos: mostrar solo activos o el producto actualmente en el carro (aunque esté desactivado)
        $productos = Producto::when(
            !$usuario->hasRole('administrador'),
            fn($q) => $q->where('estado_producto', true)
        )->get()->map(function ($producto) use ($id_carro) {
            $reservadas = CarroProducto::where('id_producto', $producto->id_producto)
                ->where('id_carro', '!=', $id_carro)
                ->sum('cantidad');
            $producto->piezas_disponibles = max(0, $producto->piezas - $reservadas);
            return $producto;
        });

        // Asegurarse de que el producto actual siempre esté en la lista (aunque esté descontinuado)
        if (!$productos->contains('id_producto', $productoActual->id_producto)) {
            $productoActual->piezas_disponibles = max(0, $productoActual->piezas -
                CarroProducto::where('id_producto', $productoActual->id_producto)
                    ->where('id_carro', '!=', $id_carro)
                    ->sum('cantidad'));
            $productos->push($productoActual);
        }

        $pedidosUsuario = Pedido::where('id_user', $carro->id_user)->get();

        return view('carro.editCarro', compact('carro', 'productoActual', 'productos', 'cantidad', 'pedidosUsuario'));
    }


    public function update(Request $request, Carro $carro, $id_producto)
    {
        $nuevoIdProducto = $request->input('id_producto');
        $cantidadSolicitada = (int) $request->input('cantidad');

        if ($cantidadSolicitada <= 0) {
            return back()->with('error', 'Cantidad no válida.');
        }

        // Obtener el nuevo producto, sin filtrar por estado
        $producto = Producto::findOrFail($nuevoIdProducto);

        // Verificar si es el mismo producto (editar cantidad)
        if ($nuevoIdProducto == $id_producto) {
            $cantidadActual = $carro->productos()->find($id_producto)->pivot->cantidad;

            // Si el producto está desactivado y se quiere aumentar la cantidad, bloquear
            if (!$producto->estado_producto && $cantidadSolicitada > $cantidadActual) {
                return back()->with('error', 'No puedes aumentar la cantidad de un producto descontinuado.');
            }

            // Verificar disponibilidad
            $reservadas = CarroProducto::where('id_producto', $id_producto)
                ->where('id_carro', '!=', $carro->id_carro)
                ->sum('cantidad');
            $disponibles = max(0, $producto->piezas - $reservadas);

            if ($cantidadSolicitada > $disponibles) {
                return back()->with('error', "Solo hay $disponibles piezas disponibles.");
            }

            $carro->productos()->updateExistingPivot($id_producto, ['cantidad' => $cantidadSolicitada]);
        } else {
            // Se intenta cambiar el producto en el carro

            // Verifica que no se repita
            if ($carro->productos()->where('productos.id_producto', $nuevoIdProducto)->exists()) {
                return back()->with('error', 'Ese producto ya está en el carro.');
            }

            // Verifica disponibilidad
            $reservadas = CarroProducto::where('id_producto', $nuevoIdProducto)
                ->where('id_carro', '!=', $carro->id_carro)
                ->sum('cantidad');
            $disponibles = max(0, $producto->piezas - $reservadas);

            if ($cantidadSolicitada > $disponibles) {
                return back()->with('error', "Solo hay $disponibles piezas disponibles.");
            }

            // Remueve el anterior y agrega el nuevo
            $carro->productos()->detach($id_producto);
            $carro->productos()->attach($nuevoIdProducto, ['cantidad' => $cantidadSolicitada]);
        }

        $carro->load('productos');

        // Recalcular total
        $pedido = $carro->pedido;
        $totalAnterior = $pedido->total_pedido;
        $nuevoTotal = $this->recalcularTotalPedido($carro);

        // Validaciones que solo aplican si el pedido está cerrado
        if ($pedido->estado_pedido == 0) {

            if (!$this->validarPedidoConCreditoVencido($pedido)) {
                return back()->with('error', 'No puedes aumentar un pedido asociado a un crédito vencido o cerrado.');
            }

            if (!$this->validarCreditoAlModificar($pedido, $nuevoTotal)) {
                return back()->with('error', 'No se puede modificar el pedido porque el crédito superaría los $10,000 o el usuario tiene más de 3 créditos activos.');
            }

            $pedido->total_pedido = $nuevoTotal;
            $pedido->save();

            $totalOriginal = $pedido->getOriginal('total_pedido');
            $this->actualizarCreditoConDiferencia($pedido, $totalOriginal, $nuevoTotal);

        }



        return redirect()->route('carro.index')->with('success', 'Carro actualizado correctamente.');
    }

    public function destroy(Carro $carro)
    {
        $pedido = $carro->pedido;
        $totalAnterior = $pedido ? $pedido->total_pedido : 0;

        $carro->productos()->detach();
        $carro->delete();

        if ($pedido) {
            $pedido->total_pedido = 0;
            $pedido->save();

            $this->actualizarCreditoConDiferencia($pedido, $totalAnterior, 0);
        }

        return redirect()->route('carro.index')->with('success', 'Carro eliminado.');
    }

    private function recalcularTotalPedido($carro)
    {
        $total = 0;
        foreach ($carro->productos as $prod) {
            $total += $prod->precio_unitario * $prod->pivot->cantidad;
        }
        return $total;
    }

    private function actualizarCreditoConDiferencia(Pedido $pedido, $totalAnterior, $nuevoTotal)
    {
        
        if ($pedido->estado_pedido == 1) {
            return;
        }

        if ($pedido->id_credito) {
            $diferencia = $nuevoTotal - $totalAnterior;
            if ($diferencia != 0) {
                $credito = Credito::find($pedido->id_credito);
                if ($credito) {
                    $credito->saldo_total += $diferencia;
                    $credito->save();
                }
            }
        }
    }

    public function show(Request $request)
    {
        $busqueda = $request->input('busqueda');
        $user = Auth::user();

        if (!$busqueda) {
            return back()->with('error', 'Debes ingresar un ID de carro o un nombre de usuario.');
        }

        // Si es búsqueda por ID de carro
        if (is_numeric($busqueda)) {
            $carro = Carro::with(['productos', 'user'])->find($busqueda);

            if (!$carro) {
                return back()->with('error', 'El carro no se encontró.');
            }

            // Validar si el usuario tiene acceso
            if (!$user->hasRole('administrador') && $carro->id_user !== $user->id_user) {
                return back()->with('error', 'No tienes permiso para ver este carro.');
            }

            return view('carro.showCarro', compact('carro'));
        }

        // Si es búsqueda por nombre de usuario (solo para admin)
        if (!$user->hasRole('administrador')) {
            return back()->with('error', 'No puedes buscar carros por nombre de usuario.');
        }

        // Obtener todos los usuarios cuyo nombre de usuario coincida parcialmente
        $usuarios = User::where('nombre_usuario', 'ILIKE', '%' . $busqueda . '%')->get();

        if ($usuarios->isEmpty()) {
            return back()->with('error', 'No se encontraron usuarios con ese nombre.');
        }

        // Obtener los carros de todos esos usuarios
        $carros = Carro::with(['productos', 'user'])
            ->whereIn('id_user', $usuarios->pluck('id_user'))
            ->get();

        if ($carros->isEmpty()) {
            return back()->with('error', 'No se encontraron carros para esos usuarios.');
        }

        return view('carro.showCarro', compact('carros'));
    }


    // private function usuarioBloqueado($id_user)
    // {
    //     $creditos = Credito::where('id_user', $id_user)->where('estado', 1)->get();
    //     return ($creditos->count() >= 3 || $creditos->sum('saldo_total') > 10000);
    // }

    private function validarCreditoAlModificar(Pedido $pedido, $nuevoTotal)
    {
        if ($pedido->metodo_pago !== 'credito' || !$pedido->id_credito) {
            return true; // No aplica la validación si no es a crédito
        }

        $user = $pedido->user;
        $creditosActivos = Credito::where('id_user', $user->id_user)->where('estado', 1)->get();

        if ($creditosActivos->count() >= 3) {
            return false;
        }

        $credito = Credito::find($pedido->id_credito);
        if (!$credito) return false;

        $nuevoSaldo = $credito->saldo_total + ($nuevoTotal - $pedido->total_pedido);

        return $nuevoSaldo <= 10000;
    }

    private function validarPedidoConCreditoVencido(Pedido $pedido)
    {
        if ($pedido->estado_pedido == 0 && $pedido->id_credito) {
            $credito = $pedido->credito;
            if ($credito && ($credito->estado == 0 || $credito->fecha_vencimiento < now())) {
                return false; // Nunca permitir modificar si el crédito está cerrado o vencido
            }
        }
        return true;
    }
}
