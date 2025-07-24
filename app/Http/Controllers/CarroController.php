<?php

namespace App\Http\Controllers;

use App\Models\Abono;
use App\Models\Carro;
use App\Models\CarroProducto;
use App\Models\Credito;
use App\Models\Pedido;
use App\Models\Producto;
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
            ? Carro::with('productos')->get()
            : Carro::with('productos')->where('id_user', $user->id_user)->get();

        $reservasGlobales = CarroProducto::select('id_producto')
            ->selectRaw('SUM(cantidad) as total_reservado')
            ->groupBy('id_producto')
            ->pluck('total_reservado', 'id_producto');

        $todosProductos = Producto::all();

        return view('carro.carroIndex', compact('carroIndex', 'reservasGlobales', 'todosProductos'));
    }

    public function create()
    {
        $usuario = Auth::user();
        if ($usuario->hasRole('administrador')) {
            $pedidosUsuario = Pedido::all();
        } else {
            $pedidosUsuario = Pedido::where('id_user', $usuario->id_user)->get(); // Solo los suyos
        }

        $reservas = DB::table('carro_productos')
            ->select('id_producto', DB::raw('SUM(cantidad) as reservadas'))
            ->groupBy('id_producto')
            ->pluck('reservadas', 'id_producto');

        $productos = Producto::all();
        foreach ($productos as $producto) {
            $producto->piezas_disponibles = $producto->piezas - ($reservas[$producto->id_producto] ?? 0);
        }

        return view('carro/createCarro', compact('usuario','pedidosUsuario', 'productos'));
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

        $reservadas = CarroProducto::where('id_producto', $productoId)->sum('cantidad');
        $disponibles = max(0, $producto->piezas - $reservadas);

        if ($cantidad > $disponibles) {
            return back()->with('error', "Solo hay $disponibles piezas disponibles.");
        }

        // Validación segura del pedido
        if ($request->has('nuevo_pedido')) {
            $pedido = Pedido::create([
                'id_user' => $userId,
                'estado_pedido' => 1,
                'metodo_pago' => 'contado',
            ]);
        } elseif ($request->filled('id_pedido')) {
            $pedido = Pedido::find($request->input('id_pedido'));

            if (!$pedido) {
                return back()->with('error', 'Pedido no encontrado.');
            }

            if ($pedido->estado_pedido == 0) {
                return back()->with('error', 'El pedido está cerrado.');
            }
        } else {
            return back()->with('error', 'Debes seleccionar un pedido o marcar "crear uno nuevo".');
        }

        // Buscar o crear carro del pedido
        $carro = Carro::firstOrCreate(
            ['id_pedido' => $pedido->id_pedido],
            ['id_user' => $userId]
        );

        // Verificar si el producto ya está en el carro
        $productoExistente = $carro->productos()->where('productos.id_producto', $productoId)->first();

        if ($productoExistente) {
            $cantidadActual = $productoExistente->pivot->cantidad;
            $carro->productos()->updateExistingPivot($productoId, [
                'cantidad' => $cantidadActual + $cantidad
            ]);
        } else {
            $carro->productos()->attach($productoId, ['cantidad' => $cantidad]);
        }

        // Recalcular total del pedido
        $total = 0;
        foreach ($carro->productos as $prod) {
            $total += $prod->precio_unitario * $prod->pivot->cantidad;
        }

        $pedido->total_pedido = $total;
        $pedido->save();

        // Si el pedido está cerrado y tiene crédito, actualiza el crédito
        if ($pedido->estado_pedido == 0 && $pedido->id_credito) {
            $credito = Credito::find($pedido->id_credito);
            $credito->saldo_total = Pedido::where('id_credito', $pedido->id_credito)->sum('total_pedido');
            $credito->save();
        }


        return redirect()->route('carro.index')->with('success', 'Producto agregado correctamente.');
    }

    public function agregarMultiples(Request $request)
    {
        $userId = $request->input('id_user');
        $idPedido = $request->input('id_pedido');
        $seleccionados = $request->input('productos_seleccionados', []);
        $cantidades = $request->input('cantidades', []);

        if ($idPedido === 'nuevo') {
            $pedido = Pedido::create([
                'id_user' => $userId,
                'estado_pedido' => 1
            ]);
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

        $carro = Carro::firstOrCreate(
            ['id_pedido' => $idPedido],
            ['id_user' => $userId]
        );

        foreach ($seleccionados as $idProducto) {
            $cantidad = (int) ($cantidades[$idProducto] ?? 0);
            if ($cantidad <= 0) continue;

            $producto = Producto::find($idProducto);
            if (!$producto) continue;

            $reservadas = CarroProducto::where('id_producto', $idProducto)->sum('cantidad');
            $disponibles = max(0, $producto->piezas - $reservadas);

            if ($cantidad > $disponibles) {
                return back()->with('error', "No hay suficientes piezas de $producto->nombre (quedan $disponibles).");
            }

            $productoEnCarro = $carro->productos()->where('productos.id_producto', $idProducto)->first();

            if ($productoEnCarro) {
                $cantidadActual = $productoEnCarro->pivot->cantidad;
                $carro->productos()->updateExistingPivot($idProducto, [
                    'cantidad' => $cantidadActual + $cantidad
                ]);
            } else {
                $carro->productos()->attach($idProducto, ['cantidad' => $cantidad]);
            }
        }

        // Recalcular total del pedido
        $total = 0;
        foreach ($carro->productos as $prod) {
            $total += $prod->precio_unitario * $prod->pivot->cantidad;
        }

        $pedido->total_pedido = $total;
        $pedido->save();

        // Si el pedido está cerrado y tiene crédito, actualiza el crédito
        if ($pedido->estado_pedido == 0 && $pedido->id_credito) {
            $credito = Credito::find($pedido->id_credito);
            $credito->saldo_total = Pedido::where('id_credito', $pedido->id_credito)->sum('total_pedido');
            $credito->save();
        }


        return redirect()->route('carro.index')->with('success', 'Productos agregados correctamente.');
    }

    public function eliminarProducto($id_carro, $id_producto)
    {
        $carro = Carro::with('pedido')->findOrFail($id_carro);
        $carro->productos()->detach($id_producto);

        if ($carro->productos()->count() == 0) {
            $carro->delete();
        }

        // Recalcular total del pedido
        $pedido = Pedido::find($carro->id_pedido);

        if ($pedido) {
            // Recalcular nuevo total
            $nuevoTotal = 0;
            foreach ($carro->productos as $prod) {
                $nuevoTotal += $prod->precio_unitario * $prod->pivot->cantidad;
            }

            $pedido->total_pedido = $nuevoTotal;
            $pedido->save();

            // Actualizar crédito si aplica
            if ($pedido->estado_pedido == 0 && $pedido->id_credito) {
                $credito = Credito::find($pedido->id_credito);
                $credito->saldo_total = Pedido::where('id_credito', $pedido->id_credito)->sum('total_pedido');
                $credito->save();
            }
        }


        return redirect()->route('carro.index')->with('success', 'Producto eliminado.');
    }


    public function show(Request $request)
    {
        $idCarro = $request->input('id_carro');
        $nombreUsuario = $request->input('nombre_usuario');

        // Buscar por ID de carro
        if ($idCarro) {
            $carro = Carro::with(['productos', 'user'])->find($idCarro);

            if (!$carro) {
                return back()->with('error', 'El carro no se encontró.');
            }

            return view('carro.showCarro', compact('carro'));
        }

        // Buscar por nombre de usuario
        if ($nombreUsuario) {
            $usuario = User::where('nombre_usuario', 'ILIKE', $nombreUsuario)->first();

            if (!$usuario) {
                return back()->with('error', 'Usuario no encontrado.');
            }

            $carros = Carro::with(['productos', 'user'])->where('id_user', $usuario->id_user)->get();

            if ($carros->isEmpty()) {
                return back()->with('error', 'No se encontraron carros para el usuario "' . $nombreUsuario . '".');
            }

            return view('carro.showCarro', compact('carros'));
        }

        return back()->with('error', 'Debes ingresar un ID de carro o un nombre de usuario.');
    }

    public function edit($id_carro, $id_producto)
    {
        $carro = Carro::findOrFail($id_carro);
        $productoActual = $carro->productos()->where('productos.id_producto', $id_producto)->firstOrFail();

        $cantidad = $productoActual->pivot->cantidad;
        $productos = Producto::all()->map(function ($producto) use ($id_carro) {
            $reservadas = CarroProducto::where('id_producto', $producto->id_producto)
                ->where('id_carro', '!=', $id_carro)
                ->sum('cantidad');
            $producto->piezas_disponibles = max(0, $producto->piezas - $reservadas);
            return $producto;
        });

        $pedidosUsuario = Pedido::where('id_user', $carro->id_user)->get();

        return view('carro.editCarro', compact('carro', 'productoActual', 'productos', 'cantidad', 'pedidosUsuario'));
    }

    public function update(Request $request, Carro $carro, $id_producto)
    {
        $nuevoIdProducto = $request->input('id_producto');
        $cantidadSolicitada = (int) $request->input('cantidad');

        if ($cantidadSolicitada <= 0) {
            return back()->with('error', 'La cantidad debe ser mayor a 0.');
        }

        $producto = Producto::findOrFail($nuevoIdProducto);

        $reservadas = CarroProducto::where('id_producto', $nuevoIdProducto)
            ->where('id_carro', '!=', $carro->id_carro)
            ->sum('cantidad');

        $disponibles = max(0, $producto->piezas - $reservadas);

        if ($cantidadSolicitada > $disponibles) {
            return back()->with('error', "Solo hay $disponibles piezas disponibles.");
        }

        // Obtener el pedido y su total antes del cambio
        $pedido = Pedido::find($carro->id_pedido);
        $totalAnterior = $pedido->total_pedido;

        if ($nuevoIdProducto == $id_producto) {
            $carro->productos()->updateExistingPivot($id_producto, ['cantidad' => $cantidadSolicitada]);
        } else {
            if ($carro->productos()->where('productos.id_producto', $nuevoIdProducto)->exists()) {
                return back()->with('error', 'Ese producto ya está en este carro.');
            }

            $carro->productos()->detach($id_producto);
            $carro->productos()->attach($nuevoIdProducto, ['cantidad' => $cantidadSolicitada]);
        }

        // Recalcular total del pedido
        $nuevoTotal = 0;
        foreach ($carro->productos as $prod) {
            $nuevoTotal += $prod->precio_unitario * $prod->pivot->cantidad;
        }

        // Actualizar pedido
        $pedido->total_pedido = $nuevoTotal;
        $pedido->save();

        // Si el pedido está cerrado y tiene crédito, actualizar el crédito
        if ($pedido->estado_pedido == 0 && $pedido->id_credito) {
            $credito = Credito::find($pedido->id_credito);

            $diferencia = $nuevoTotal - $totalAnterior;

            if ($diferencia > 0) {
                $credito->saldo_total += $diferencia;
                $credito->save();
            }
        }

        return redirect()->route('carro.index')->with('success', 'Carro actualizado correctamente.');
    }


    public function destroy(Carro $carro)
    {
        $pedido = Pedido::find($carro->id_pedido);

        $carro->productos()->detach();
        $carro->delete();

        if ($pedido) {
            $pedido->total_pedido = 0;
            $pedido->save();

            if ($pedido->estado_pedido == 0 && $pedido->id_credito) {
                $credito = Credito::find($pedido->id_credito);
                $credito->saldo_total = Pedido::where('id_credito', $pedido->id_credito)->sum('total_pedido');
                $credito->save();
            }
        }

        return redirect()->route('carro.index')->with('success', 'Carro eliminado.');
    }

}
