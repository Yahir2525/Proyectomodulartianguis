<?php

namespace App\Http\Controllers;

use App\Models\Abono;
use App\Models\Carro;
use App\Models\CarroProducto;
use App\Models\Credito;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class CarroController extends Controller
{

    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Carro::with(['productos', 'user', 'pedido']);

        if ($request->filled('buscar')) {
            $busqueda = $request->input('buscar');

            if (is_numeric($busqueda)) {
                $query->where('id_carro', 'LIKE', "%{$busqueda}%");
            } else if ($user->hasRole('administrador')) {
                $idsUsuarios = User::where('nombre_usuario', 'ILIKE', "%{$busqueda}%")->pluck('id_user');
                $query->whereIn('id_user', $idsUsuarios);
            } else {
                return back()->with('error', 'Solo puedes buscar tus carros por ID.');
            }
        }

        if (!$user->hasRole('administrador')) {
            $query->where('id_user', $user->id_user);
        }

        $carroIndex = $query->orderBy('id_carro')->paginate(5)->withQueryString();

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
        $pedidos = Pedido::where('estado_pedido', 1)->get();

        $seleccion = $request->input('sel', []);

        $query = Producto::query();

        if ($request->filled('buscar')) {
            $busqueda = $request->input('buscar');

            if (is_numeric($busqueda)) {
                $query->where('id_producto', 'LIKE', "%{$busqueda}%");
            } else {
                $query->where(function ($q) use ($busqueda) {
                    $q->where('nombre', 'ILIKE', "%{$busqueda}%")
                    ->orWhere('material', 'ILIKE', "%{$busqueda}%")
                    ->orWhere('color', 'ILIKE', "%{$busqueda}%")
                    ->orWhere('tamanio', 'ILIKE', "%{$busqueda}%");
                });
            }
        }

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

        $productos = $query
            ->orderBy('tipo')
            ->orderBy('id_producto')
            ->paginate(10)
            ->withQueryString();

        $reservas = DB::table('carro_productos')
            ->select('id_producto', DB::raw('SUM(cantidad) as reservadas'))
            ->groupBy('id_producto')
            ->pluck('reservadas', 'id_producto');

        foreach ($productos as $producto) {
            $producto->piezas_disponibles = $producto->piezas - ($reservas[$producto->id_producto] ?? 0);
        }

        $tipos      = Producto::select('tipo')->distinct()->pluck('tipo');
        $materiales = Producto::select('material')->distinct()->pluck('material');
        $colores    = Producto::select('color')->distinct()->pluck('color');
        $tamanios   = Producto::select('tamanio')->distinct()->pluck('tamanio');

        $nombresUnicos = Producto::select('nombre')
            ->distinct()
            ->orderBy('nombre')
            ->limit(7)
            ->pluck('nombre');

        return view('carro/createCarro', compact(
            'usuario',
            'usuarios',
            'pedidos',
            'productos',
            'tipos',
            'materiales',
            'colores',
            'tamanios',
            'nombresUnicos',
            'seleccion'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_user'     => 'required|exists:users,id_user',
            'id_producto' => 'required|exists:productos,id_producto',
            'cantidad'    => 'required|integer|min:1',
            
        ], [
            'id_user.required'     => 'El usuario es obligatorio.',
            'id_user.exists'       => 'El usuario seleccionado no existe.',
            
            'id_producto.required' => 'El producto es obligatorio.',
            'id_producto.exists'   => 'El producto seleccionado no existe.',
            
            'cantidad.required'    => 'La cantidad es obligatoria.',
            'cantidad.integer'     => 'La cantidad debe ser un número entero.',
            'cantidad.min'         => 'La cantidad debe ser al menos 1.',
            
        ]);
        
        $userId = $request->input('id_user');
        $productoId = $request->input('id_producto');
        $cantidad = (int) $request->input('cantidad');

        if ($cantidad <= 0) {
            return back()->with('error', 'La cantidad debe ser mayor a 0.');
        }

        $producto = Producto::findOrFail($productoId);

        // Validar que el producto esté activo
        if (!$producto->estado_producto) {
            return back()->with('error', 'Este producto está inactivo y no puede ser agregado.');
        }

        $user = User::findOrFail($userId);

        if ($request->input('id_pedido') === 'nuevo') {
            // crear un nuevo pedido
            $pedido = Pedido::create([
                'id_user' => $carro->id_user,
                'estado_pedido' => 1,
                'metodo_pago' => 'contado',
                'total_pedido' => 0,
            ]);
            $carro->id_pedido = $pedido->id_pedido;
            $carro->save();
        } elseif ($request->filled('id_pedido')) {
            // usar un pedido existente
            $pedido = Pedido::find($request->input('id_pedido'));
            if (!$pedido || $pedido->estado_pedido == 0) {
                return back()->with('error', 'El pedido está cerrado o no existe.');
            }
            $carro->id_pedido = $pedido->id_pedido;
            $carro->save();
        } else {
            // mantener el actual
            $pedido = $carro->pedido;
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

        $userId     = $request->input('id_user');
        $idPedido   = $request->input('id_pedido');

        $seleccionados = $request->input('productos_seleccionados', []);
        $cantidades    = $request->input('cantidades', []);

        $seleccionados = array_values(array_unique(array_map('strval', $seleccionados)));

        $idsPorCantidad    = array_keys(array_filter($cantidades, fn($c) => (int)$c > 0));
        $seleccionEfectiva = array_values(array_unique(array_map('strval', array_merge($seleccionados, $idsPorCantidad))));

        if (empty($seleccionEfectiva)) {
            return back()->with('error', 'No seleccionaste ningún producto.');
        }

        $user = User::findOrFail($userId);

        if ($idPedido === 'nuevo') {
            $pedido   = Pedido::create(['id_user' => $userId, 'estado_pedido' => 1]);
            $idPedido = $pedido->id_pedido;
        } else {
            $pedido = Pedido::find($idPedido);
            if (!$pedido || $pedido->estado_pedido == 0) {
                return back()->with('error', 'No se puede usar un pedido cerrado.');
            }
        }

        // Calcular nuevo total sumando productos * cantidades
        $nuevoTotal = $pedido->total_pedido;
        foreach ($seleccionEfectiva as $idProducto) {
            $producto = Producto::find($idProducto);
            if (!$producto) continue;
            $cantidad = (int)($cantidades[$idProducto] ?? 0);
            if ($cantidad <= 0) continue;
            $nuevoTotal += $producto->precio_unitario * $cantidad;
        }

        // Reglas de comportamiento de pago
        if ($user->nivel_usuario === 'malo') {
            if ($request->input('metodo_pago') === 'credito') {
                return back()->with('error', 'No puedes aumentar el total del pedido a crédito porque tienes pagos atrasados sin abonar.');
            }
        }

        $carro = Carro::firstOrCreate(
            ['id_pedido' => $pedido->id_pedido],
            ['id_user'   => $pedido->id_user]
        );

        // Validar inactivos
        foreach ($seleccionEfectiva as $idProducto) {
            $producto = Producto::find($idProducto);
            if (!$producto) {
                return back()->with('error', "El producto con ID $idProducto no existe.");
            }
            if (!$producto->estado_producto) {
                return back()->with('error', "El producto \"{$producto->nombre}\" está inactivo y no puede ser agregado.");
            }
        }

        $totalAnterior = $pedido->total_pedido;

        // Agregar/actualizar productos
        foreach ($seleccionEfectiva as $idProducto) {
            $cantidad = (int)($cantidades[$idProducto] ?? 0);
            if ($cantidad <= 0) continue;

            $producto    = Producto::find($idProducto);
            $reservadas  = CarroProducto::where('id_producto', $idProducto)->sum('cantidad');
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

        $query = Producto::query();

        if ($request->filled('buscar')) {
            $busqueda = $request->input('buscar');

            if (is_numeric($busqueda)) {
                $query->where('id_producto', 'LIKE', "%{$busqueda}%");
            } else {
                $query->where(function ($q) use ($busqueda) {
                    $q->where('nombre', 'ILIKE', "%{$busqueda}%")
                    ->orWhere('material', 'ILIKE', "%{$busqueda}%")
                    ->orWhere('color', 'ILIKE', "%{$busqueda}%")
                    ->orWhere('tamanio', 'ILIKE', "%{$busqueda}%");
                });
            }
        }

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

        $perPage = 10;
        $selId   = (int) $request->input('sel_id', $productoActual->id_producto);
        $selQty  = (int) $request->input('sel_qty', $cantidad);
        if ($selQty < 1) { $selQty = 1; }

        $allIds   = (clone $query)->orderBy('tipo')->orderBy('id_producto')->pluck('id_producto');
        $posicion = $allIds->search($selId);

        $paginaCorrecta = null;
        if ($posicion !== false && !$request->has('navegacion')) {
            $paginaCorrecta   = (int) ceil(($posicion + 1) / $perPage);
            $paginaSolicitada = (int) $request->input('page', 1);

            if ($paginaSolicitada != $paginaCorrecta) {
                return redirect()->route('carro.edit', [
                    'id_carro'    => $id_carro,
                    'id_producto' => $id_producto,
                    'page'        => $paginaCorrecta,
                    'navegacion'  => 1,
                    'sel_id'      => $selId,
                    'sel_qty'     => $selQty,
                ] + $request->query());
            }
        } elseif ($posicion !== false) {
            $paginaCorrecta = (int) ceil(($posicion + 1) / $perPage);
        }

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

        $tipos      = Producto::select('tipo')->distinct()->pluck('tipo');
        $materiales = Producto::select('material')->distinct()->pluck('material');
        $colores    = Producto::select('color')->distinct()->pluck('color');
        $tamanios   = Producto::select('tamanio')->distinct()->pluck('tamanio');

        $nombresUnicos = Producto::select('nombre')
            ->distinct()
            ->orderBy('nombre')
            ->limit(7)
            ->pluck('nombre');

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
            'tamanios',
            'nombresUnicos',
            'paginaCorrecta'
        ) + [
            'selId'  => $selId,
            'selQty' => $selQty,
        ]);
    }

    public function update(Request $request, Carro $carro, $id_producto)
    {
        $request->validate([
            'id_producto' => 'required|exists:productos,id_producto',
            'cantidad'    => 'required|integer|min:1',
        ], [
            'id_producto.required' => 'El producto es obligatorio.',
            'id_producto.exists'   => 'El producto seleccionado no existe.',

            'cantidad.required'    => 'La cantidad es obligatoria.',
            'cantidad.integer'     => 'La cantidad debe ser un número entero.',
            'cantidad.min'         => 'La cantidad debe ser al menos 1.',

        ]);

        $nuevoIdProducto = $request->input('id_producto');
        $cantidadSolicitada = (int) $request->input('cantidad');

        if ($cantidadSolicitada <= 0) {
            return back()->with('error', 'Cantidad no válida.');
        }

        if ($request->input('id_pedido') === 'nuevo') {
            // crear un nuevo pedido
            $pedidoNuevo = Pedido::create([
                'id_user' => $carro->id_user,
                'estado_pedido' => 1,
                'metodo_pago' => 'contado',
                'total_pedido' => 0,
            ]);

            // crear un nuevo carro para ese pedido
            $carroNuevo = Carro::create([
                'id_user' => $carro->id_user,
                'id_pedido' => $pedidoNuevo->id_pedido,
            ]);

            // mover solo el producto seleccionado
            $carro->productos()->detach($id_producto);
            $carroNuevo->productos()->attach($nuevoIdProducto, ['cantidad' => $cantidadSolicitada]);

            // recalcular totales
            $this->recalcularTotalPedido($carro);
            $this->recalcularTotalPedido($carroNuevo);

            return redirect()->route('carro.index')->with('success', 'Producto movido a un nuevo pedido.');
        } elseif ($request->filled('id_pedido')) {
            $pedido = Pedido::find($request->input('id_pedido'));
            if (!$pedido || $pedido->estado_pedido == 0) {
                return back()->with('error', 'El pedido está cerrado o no existe.');
            }

            if ($pedido->id_credito) {
                $credito = Credito::find($pedido->id_credito);

                if (!$credito || $credito->estado == 0 || now()->greaterThan($credito->fecha_vencimiento)) {
                    return back()->with('error', 'No puedes mover productos a un pedido con crédito cerrado o vencido.');
                }
            }

            $carroDestino = Carro::firstOrCreate([
                'id_pedido' => $pedido->id_pedido,
                'id_user' => $carro->id_user,
            ]);

            $carro->productos()->detach($id_producto);
            $carroDestino->productos()->attach($nuevoIdProducto, ['cantidad' => $cantidadSolicitada]);

            $this->recalcularTotalPedido($carro);
            $this->recalcularTotalPedido($carroDestino);

            return redirect()->route('carro.index')->with('success', 'Producto movido a otro pedido.');
        } else {
            $pedido = $carro->pedido;

            if ($pedido && $pedido->id_credito) {
                $credito = Credito::find($pedido->id_credito);

                if (!$credito || $credito->estado == 0 || now()->greaterThan($credito->fecha_vencimiento)) {
                    return back()->with('error', 'No puedes modificar productos en un pedido con crédito cerrado o vencido.');
                }
            }
        }

        $producto = Producto::findOrFail($nuevoIdProducto);

        if ($nuevoIdProducto == $id_producto) {
            $cantidadActual = $carro->productos()->find($id_producto)->pivot->cantidad;

            if (!$producto->estado_producto && $cantidadSolicitada > $cantidadActual) {
                return back()->with('error', 'No puedes aumentar la cantidad de un producto inactivo.');
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

        $totalAnterior = $pedido->total_pedido ?? 0;
        $nuevoTotal = $this->recalcularTotalPedido($carro);

        if (isset($pedido) && $pedido->estado_pedido == 0) {
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

        $pedido = $carro->pedido;

        if ($pedido) {
            $totalAnterior = $pedido->getOriginal('total_pedido') ?? 0;

            // Actualizar el pedido con el nuevo total
            $pedido->total_pedido = $total;
            $pedido->save();

            // Si el pedido quedó vacío y tenía un crédito
            if ($total == 0 && $pedido->id_credito) {
                $credito = Credito::find($pedido->id_credito);
                if ($credito) {
                    // Restar el total anterior al saldo
                    $credito->saldo_total = max(0, $credito->saldo_total - $totalAnterior);

                    // Si el crédito ya está en 0, cerrarlo y marcar fecha de liquidación
                    if ($credito->saldo_total == 0) {
                        $credito->estado = 0;
                        $credito->fecha_liquidacion = now();
                    }

                    $credito->save();
                }
                // Quitar relación del pedido con el crédito
                $pedido->id_credito = null;
                $pedido->metodo_pago = null;
                $pedido->save();
            }
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
        $busqueda = $request->input('buscar');
        $user = Auth::user();

        if (!$busqueda) {
            return redirect()->route('carro.index')
            ->with('info', 'Se mostró la lista completa porque no ingresaste ningún criterio de búsqueda.');
        }

        if (is_numeric($busqueda)) {
            $carrosQuery = Carro::with(['productos', 'user', 'pedido'])
                ->where('id_carro', 'LIKE', "%{$busqueda}%");

            if (!$user->hasRole('administrador')) {
                $carrosQuery->where('id_user', $user->id_user);
            }

            $carros = $carrosQuery->orderBy('id_carro')->paginate(5)->withQueryString();

            if ($carros->isEmpty()) {
                return back()->with('error', 'No se encontraron carros con ese ID.');
            }

            $pedidosUsuario = Pedido::whereIn('id_user', $carros->pluck('id_user'))->get();
            $usuarios = $user->hasRole('administrador') ? User::all() : collect();

            return view('carro.showCarro', compact('carros', 'pedidosUsuario', 'usuarios'));
        }

        if (!$user->hasRole('administrador')) {
            return back()->with('error', 'No puedes buscar carros por nombre de usuario.');
        }

        $usuariosQuery = User::where('nombre_usuario', 'ILIKE', "%{$busqueda}%")->get();

        if ($usuariosQuery->isEmpty()) {
            return back()->with('error', 'No se encontraron usuarios con ese nombre.');
        }

        $carros = Carro::with(['productos', 'user', 'pedido'])
            ->whereIn('id_user', $usuariosQuery->pluck('id_user'))
            ->orderBy('id_carro')
            ->paginate(5)
            ->withQueryString();

        if ($carros->isEmpty()) {
            return back()->with('error', 'No se encontraron carros para esos usuarios.');
        }

        $pedidosUsuario = Pedido::whereIn('id_user', $usuariosQuery->pluck('id_user'))->get();
        $usuarios = User::all();

        return view('carro.showCarro', compact('carros', 'pedidosUsuario', 'usuarios'));
    }

    private function validarCreditoAlModificar(Pedido $pedido, $nuevoTotal)
    {
        if ($pedido->metodo_pago !== 'credito' || !$pedido->id_credito) {
            return true;
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
                return false;
            }
        }
        return true;
    }
}
