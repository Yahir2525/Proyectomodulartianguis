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
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class CarroController extends Controller
{

    public function index()
    {
        $user = Auth::user();

        $carroIndex = $user->hasRole('administrador')
            ? Carro::with(['productos', 'user', 'pedido'])->paginate(5) // 🔹 ahora paginados de 5 en 5
            : Carro::with(['productos', 'user', 'pedido'])
                ->where('id_user', $user->id_user)
                ->paginate(5);

        $reservasGlobales = CarroProducto::select('id_producto')
            ->selectRaw('SUM(cantidad) as total_reservado')
            ->groupBy('id_producto')
            ->pluck('total_reservado', 'id_producto');

        $todosProductos = Producto::all();

        $usuarios = $user->hasRole('administrador') ? User::all() : collect();

        return view('carro.carroIndex', compact('carroIndex', 'reservasGlobales', 'todosProductos', 'usuarios'));
    }


    public function create(Request $request)
    {
            $usuario = Auth::user();

            $usuarios = $usuario->hasRole('administrador') ? User::all() : collect();
            $pedidos = Pedido::all();

            // Base query
            $query = Producto::query();

            // === 🔍 Buscador por ID, nombre, material, color o tamaño ===
            if ($busqueda = $request->input('buscar')) {
                if (is_numeric($busqueda)) {
                    $query->where('id_producto', $busqueda);
                } else {
                    $query->where(function ($q) use ($busqueda) {
                        $q->where('nombre', 'ILIKE', "%{$busqueda}%")
                        ->orWhere('material', 'ILIKE', "%{$busqueda}%")
                        ->orWhere('color', 'ILIKE', "%{$busqueda}%")
                        ->orWhere('tamanio', 'ILIKE', "%{$busqueda}%");
                    });
                }
            }

            // === Filtros adicionales (igual que en producto.index) ===
            if ($request->filled('tipo')) {
                $query->where('tipo', $request->tipo);
            }
            if ($request->filled('material')) {
                $query->where('material', $request->material);
            }
            if ($request->filled('color')) {
                $query->where('color', $request->color);
            }
            if ($request->filled('tamanio')) {
                $query->where('tamanio', $request->tamanio);
            }
            if ($request->filled('precio_min')) {
                $query->where('precio_unitario', '>=', $request->precio_min);
            }
            if ($request->filled('precio_max')) {
                $query->where('precio_unitario', '<=', $request->precio_max);
            }
            if ($request->filled('estado')) {
                $query->where('estado_producto', $request->estado);
            }

            // Paginación
            $productos = $query
                ->orderBy('tipo')
                ->orderBy('id_producto')
                ->paginate(10)
                ->withQueryString();

            // Calcular reservas y piezas disponibles
            $reservas = DB::table('carro_productos')
                ->select('id_producto', DB::raw('SUM(cantidad) as reservadas'))
                ->groupBy('id_producto')
                ->pluck('reservadas', 'id_producto');

            foreach ($productos as $producto) {
                $producto->piezas_disponibles = $producto->piezas - ($reservas[$producto->id_producto] ?? 0);
            }

            // === Opciones únicas para filtros ===
            $tipos      = Producto::select('tipo')->distinct()->pluck('tipo');
            $materiales = Producto::select('material')->distinct()->pluck('material');
            $colores    = Producto::select('color')->distinct()->pluck('color');
            $tamanios   = Producto::select('tamanio')->distinct()->pluck('tamanio');

            return view('carro/createCarro', compact(
                'usuario',
                'usuarios',
                'pedidos',
                'productos',
                'tipos',
                'materiales',
                'colores',
                'tamanios'
            ));
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
        if ($user->nivel_usuario === 'malo') {
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
        if ($user->nivel_usuario === 'malo') {
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

    public function edit(Request $request, $id_carro, $id_producto)
    {
        $carro = Carro::findOrFail($id_carro);
        $productoActual = $carro->productos()->where('productos.id_producto', $id_producto)->firstOrFail();
        $cantidad = $productoActual->pivot->cantidad;

        $usuario = $carro->user;

        // Base query de productos
        $query = Producto::query();

        // === 🔍 Buscador por ID, nombre, material, color o tamaño ===
        if ($busqueda = $request->input('buscar')) {
            if (is_numeric($busqueda)) {
                $query->where('id_producto', $busqueda);
            } else {
                $query->where(function ($q) use ($busqueda) {
                    $q->where('nombre', 'ILIKE', "%{$busqueda}%")
                    ->orWhere('material', 'ILIKE', "%{$busqueda}%")
                    ->orWhere('color', 'ILIKE', "%{$busqueda}%")
                    ->orWhere('tamanio', 'ILIKE', "%{$busqueda}%");
                });
            }
        }

        // === Filtros adicionales ===
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        if ($request->filled('material')) {
            $query->where('material', $request->material);
        }
        if ($request->filled('color')) {
            $query->where('color', $request->color);
        }
        if ($request->filled('tamanio')) {
            $query->where('tamanio', $request->tamanio);
        }
        if ($request->filled('precio_min')) {
            $query->where('precio_unitario', '>=', $request->precio_min);
        }
        if ($request->filled('precio_max')) {
            $query->where('precio_unitario', '<=', $request->precio_max);
        }
        if ($request->filled('estado')) {
            $query->where('estado_producto', $request->estado);
        }

        // === 📌 Calcular página real del producto actual ===
        $perPage = 10;
        $allIds = (clone $query)->orderBy('tipo')->orderBy('id_producto')->pluck('id_producto');
        $posicion = $allIds->search($productoActual->id_producto);

        if ($posicion !== false) {
            $paginaCorrecta = ceil(($posicion + 1) / $perPage);
            $paginaSolicitada = $request->input('page', 1);

            if ($paginaSolicitada != $paginaCorrecta) {
                return redirect()->route('carro.edit', [
                    'id_carro' => $id_carro,
                    'id_producto' => $id_producto,
                    'page' => $paginaCorrecta
                ] + $request->query());
            }
        }

        // Paginación de 10 en 10
        $productos = $query
            ->orderBy('tipo')
            ->orderBy('id_producto')
            ->paginate($perPage)
            ->withQueryString();

        // Calcular reservas y piezas disponibles
        foreach ($productos as $producto) {
            $reservadas = CarroProducto::where('id_producto', $producto->id_producto)
                ->where('id_carro', '!=', $id_carro)
                ->sum('cantidad');
            $producto->piezas_disponibles = max(0, $producto->piezas - $reservadas);
        }

        // Opciones únicas para filtros
        $tipos      = Producto::select('tipo')->distinct()->pluck('tipo');
        $materiales = Producto::select('material')->distinct()->pluck('material');
        $colores    = Producto::select('color')->distinct()->pluck('color');
        $tamanios   = Producto::select('tamanio')->distinct()->pluck('tamanio');

        $pedidosUsuario = Pedido::where('id_user', $carro->id_user)->get();

        return view('carro.editCarro', compact(
            'carro',
            'productoActual',
            'productos',
            'cantidad',
            'pedidosUsuario',
            'tipos',
            'materiales',
            'colores',
            'tamanios'
        ));
    }

    public function update(Request $request, Carro $carro, $id_producto)
    {
        $nuevoIdProducto = $request->input('id_producto');
        $cantidadSolicitada = (int) $request->input('cantidad');

        if ($cantidadSolicitada <= 0) {
            return back()->with('error', 'Cantidad no válida.');
        }

        // === Manejar nuevo pedido (igual que en create) ===
        if ($request->has('nuevo_pedido')) {
            $pedido = Pedido::create([
                'id_user' => $carro->id_user,
                'estado_pedido' => 1,
                'metodo_pago' => 'contado',
            ]);
            $carro->id_pedido = $pedido->id_pedido;
            $carro->save();
        } elseif ($request->filled('id_pedido')) {
            $pedido = Pedido::find($request->input('id_pedido'));
            if (!$pedido || $pedido->estado_pedido == 0) {
                return back()->with('error', 'El pedido está cerrado o no existe.');
            }
            $carro->id_pedido = $pedido->id_pedido;
            $carro->save();
        } else {
            $pedido = $carro->pedido; // usar el actual
        }

        // === Validar producto ===
        $producto = Producto::findOrFail($nuevoIdProducto);

        if ($nuevoIdProducto == $id_producto) {
            $cantidadActual = $carro->productos()->find($id_producto)->pivot->cantidad;

            if (!$producto->estado_producto && $cantidadSolicitada > $cantidadActual) {
                return back()->with('error', 'No puedes aumentar la cantidad de un producto descontinuado.');
            }

            $reservadas = CarroProducto::where('id_producto', $id_producto)
                ->where('id_carro', '!=', $carro->id_carro)
                ->sum('cantidad');
            $disponibles = max(0, $producto->piezas - $reservadas);

            if ($cantidadSolicitada > $disponibles) {
                return back()->with('error', "Solo hay $disponibles piezas disponibles.");
            }

            $carro->productos()->updateExistingPivot($id_producto, ['cantidad' => $cantidadSolicitada]);
        } else {
            if ($carro->productos()->where('productos.id_producto', $nuevoIdProducto)->exists()) {
                return back()->with('error', 'Ese producto ya está en el carro.');
            }

            $reservadas = CarroProducto::where('id_producto', $nuevoIdProducto)
                ->where('id_carro', '!=', $carro->id_carro)
                ->sum('cantidad');
            $disponibles = max(0, $producto->piezas - $reservadas);

            if ($cantidadSolicitada > $disponibles) {
                return back()->with('error', "Solo hay $disponibles piezas disponibles.");
            }

            $carro->productos()->detach($id_producto);
            $carro->productos()->attach($nuevoIdProducto, ['cantidad' => $cantidadSolicitada]);
        }

        $carro->load('productos');

        // === Recalcular total ===
        $totalAnterior = $pedido->total_pedido;
        $nuevoTotal = $this->recalcularTotalPedido($carro);

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
            $carro = Carro::with(['productos', 'user', 'pedido'])->find($busqueda);

            if (!$carro) {
                return back()->with('error', 'El carro no se encontró.');
            }

            // Validar acceso
            if (!$user->hasRole('administrador') && $carro->id_user !== $user->id_user) {
                return back()->with('error', 'No tienes permiso para ver este carro.');
            }

            $carros = new \Illuminate\Pagination\LengthAwarePaginator(
                [$carro],
                1,
                5,
                $request->input('page', 1),
                ['path' => $request->url(), 'query' => $request->query()]
            );

            // Traer todos los pedidos del usuario sin paginar
            $pedidosUsuario = Pedido::where('id_user', $carro->id_user)->get();

            return view('carro.showCarro', compact('carros', 'pedidosUsuario'));
        }

        // Si es búsqueda por nombre de usuario (solo admin)
        if (!$user->hasRole('administrador')) {
            return back()->with('error', 'No puedes buscar carros por nombre de usuario.');
        }

        $usuarios = User::where('nombre_usuario', 'ILIKE', '%' . $busqueda . '%')->get();

        if ($usuarios->isEmpty()) {
            return back()->with('error', 'No se encontraron usuarios con ese nombre.');
        }

        // 🔹 Paginación de carros (5 por página)
        $carros = Carro::with(['productos', 'user', 'pedido'])
            ->whereIn('id_user', $usuarios->pluck('id_user'))
            ->orderBy('id_carro')
            ->paginate(5)
            ->withQueryString();

        if ($carros->isEmpty()) {
            return back()->with('error', 'No se encontraron carros para esos usuarios.');
        }

        // Traer todos los pedidos de esos usuarios sin paginar
        $pedidosUsuario = Pedido::whereIn('id_user', $usuarios->pluck('id_user'))->get();

        return view('carro.showCarro', compact('carros', 'pedidosUsuario'));
    }


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
