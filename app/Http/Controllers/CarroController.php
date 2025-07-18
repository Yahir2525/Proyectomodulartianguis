<?php

namespace App\Http\Controllers;

use App\Models\Carro;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use App\Models\Abono;
use App\Models\Compra;
use App\Models\Credito;
use App\Models\CarroProducto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CarroController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $carroIndex = Carro::with('productos')->where('id_user', $userId)->get();

        $reservasGlobales = CarroProducto::select('id_producto')
            ->selectRaw('SUM(cantidad) as total_reservado')
            ->groupBy('id_producto')
            ->pluck('total_reservado', 'id_producto');

        $todosProductos = Producto::all();

    return view('carro/carroIndex', compact('carroIndex', 'reservasGlobales', 'todosProductos'));

    }

    public function create()
    {
        $usuarioId = Auth::id();
        $pedidosUsuario = Pedido::where('id_user', $usuarioId)->get();

        $reservas = DB::table('carro_productos')
            ->select('id_producto', DB::raw('SUM(cantidad) as reservadas'))
            ->groupBy('id_producto')
            ->pluck('reservadas', 'id_producto');

        $productos = Producto::all();
        foreach ($productos as $producto) {
            $producto->piezas_disponibles = $producto->piezas - ($reservas[$producto->id_producto] ?? 0);
        }

        return view('carro/createCarro', compact('usuarioId', 'pedidosUsuario', 'productos'));
    }

    public function store(Request $request)
    {
        $userId = $request->input('id_user');
        $productoId = $request->input('id_producto');
        $cantidad = (int) $request->input('cantidad');

        $id_pedido = $request->input('id_pedido');

        // Verifica si el pedido está cerrado
        if ($id_pedido) {
            $pedido = Pedido::find($id_pedido);
            if ($pedido && $pedido->estado_pedido == 0) {
                return redirect()->back()->with('error', 'No se puede agregar productos a un pedido cerrado.');
            }
        }

        if ($cantidad <= 0) {
            return back()->with('error', 'La cantidad debe ser mayor a 0.');
        }

        $producto = Producto::findOrFail($productoId);

        // Verificar disponibilidad
        $reservadas = DB::table('carro_productos')
            ->where('id_producto', $productoId)
            ->sum('cantidad');

        $disponibles = max(0, $producto->piezas - $reservadas);

        if ($cantidad > $disponibles) {
            return back()->with('error', "Solo hay $disponibles piezas disponibles.");
        }

        // Crear nuevo pedido si aplica
        if ($request->has('nuevo_pedido')) {
            $pedido = new Pedido();
            $pedido->id_user = $userId;
            $pedido->estado_pedido = 1;
            $pedido->metodo_pago = 'contado';
            $pedido->save();
        } else {
            $pedidoId = $request->input('id_pedido');
            if (!$pedidoId) {
                return back()->with('error', 'Debes seleccionar un pedido o marcar "crear uno nuevo".');
            }

            $pedido = Pedido::findOrFail($pedidoId);
        }

        // Buscar si ya existe un carro en ese pedido con ese producto
        $carrosDelPedido = Carro::where('id_pedido', $pedido->id_pedido)->get();

        foreach ($carrosDelPedido as $carrito) {
            $productoEnCarro = $carrito->productos()->where('productos.id_producto', $productoId)->first();
            if ($productoEnCarro) {
                // Sumar cantidad al carro existente
                $cantidadActual = $productoEnCarro->pivot->cantidad;

                $carrito->productos()->updateExistingPivot($productoId, [
                    'cantidad' => $cantidadActual + $cantidad
                ]);

                return redirect()->route('carro.index')->with('success', 'Cantidad actualizada en el carrito existente.');
            }
        }

        // Si no existe, crea un nuevo carro
        $carro = new Carro();
        $carro->id_user = $userId;
        $carro->id_pedido = $pedido->id_pedido;
        $carro->save();

        DB::table('carro_productos')->insert([
            'id_carro' => $carro->id_carro,
            'id_producto' => $productoId,
            'cantidad' => $cantidad
        ]);

        return redirect()->route('carro.index')->with('success', 'Producto agregado correctamente al carrito.');
    }




    public function edit($id_carro, $id_producto)
    {
        $carro = Carro::findOrFail($id_carro);

        // Producto actual que se va a editar
        $productoActual = $carro->productos()->where('productos.id_producto', $id_producto)->firstOrFail();
        $cantidad = $productoActual->pivot->cantidad;

        // Cargar todos los productos y calcular su disponibilidad
        $productos = Producto::all()->map(function ($producto) use ($id_carro) {
            $reservadas = DB::table('carro_productos')
                ->where('id_producto', $producto->id_producto)
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
        $id_pedido = $request->input('id_pedido');

        // Verifica si el pedido está cerrado
        if ($id_pedido) {
            $pedido = Pedido::find($id_pedido);
            if ($pedido && $pedido->estado_pedido == 0) {
                return redirect()->back()->with('error', 'No se puede agregar productos a un pedido cerrado.');
            }
        }

        $nuevoIdProducto = $request->input('id_producto');
        $cantidadSolicitada = (int) $request->input('cantidad');

        if ($cantidadSolicitada <= 0) {
            return back()->with('error', 'La cantidad debe ser mayor a 0.');
        }

        $producto = Producto::findOrFail($nuevoIdProducto);

        // Calcular cuántas piezas están reservadas por otros carros
        $reservadas = DB::table('carro_productos')
            ->where('id_producto', $nuevoIdProducto)
            ->where('id_carro', '!=', $carro->id_carro)
            ->sum('cantidad');

        $disponibles = max(0, $producto->piezas - $reservadas);
        if ($cantidadSolicitada > $disponibles) {
            return back()->with('error', "Solo hay $disponibles piezas disponibles.");
        }

        // Obtener el pedido
        $nuevoPedidoId = null;

        if ($request->has('nuevo_pedido')) {
            $nuevoPedido = new Pedido();
            $nuevoPedido->id_user = $carro->id_user;
            $nuevoPedido->estado_pedido = 1;
            $nuevoPedido->metodo_pago = 'contado';
            $nuevoPedido->save();
            $nuevoPedidoId = $nuevoPedido->id_pedido;
        } elseif ($request->filled('id_pedido')) {
            $nuevoPedidoId = $request->input('id_pedido');
        } else {
            $nuevoPedidoId = $carro->id_pedido;
        }

        // Si cambió el pedido, se crea un nuevo carro
        if ($nuevoPedidoId != $carro->id_pedido) {
            // Verificar que ese producto no esté ya en otro carro de ese pedido
            $yaExiste = DB::table('carros as c')
                ->join('carro_productos as cp', 'cp.id_carro', '=', 'c.id_carro')
                ->where('c.id_pedido', $nuevoPedidoId)
                ->where('cp.id_producto', $nuevoIdProducto)
                ->exists();

            if ($yaExiste) {
                return back()->with('error', 'Ese producto ya está en otro carro del pedido seleccionado.');
            }

            // Crear nuevo carro y asociar producto
            $nuevoCarro = new Carro();
            $nuevoCarro->id_user = $carro->id_user;
            $nuevoCarro->id_pedido = $nuevoPedidoId;
            $nuevoCarro->save();

            DB::table('carro_productos')->insert([
                'id_carro' => $nuevoCarro->id_carro,
                'id_producto' => $nuevoIdProducto,
                'cantidad' => $cantidadSolicitada
            ]);

            // Eliminar el producto anterior del carro original
            DB::table('carro_productos')
                ->where('id_carro', $carro->id_carro)
                ->where('id_producto', $id_producto)
                ->delete();

        } else {
            // Mismo carro, cambiar producto y cantidad
            if ($nuevoIdProducto == $id_producto) {
                // Solo actualiza cantidad
                DB::table('carro_productos')
                    ->where('id_carro', $carro->id_carro)
                    ->where('id_producto', $id_producto)
                    ->update(['cantidad' => $cantidadSolicitada]);
            } else {
                // Verificar si ya existe ese nuevo producto en este carro
                $yaExiste = DB::table('carro_productos')
                    ->where('id_carro', $carro->id_carro)
                    ->where('id_producto', $nuevoIdProducto)
                    ->exists();

                if ($yaExiste) {
                    return back()->with('error', 'Ese producto ya está en este carro.');
                }

                DB::table('carro_productos')
                    ->where('id_carro', $carro->id_carro)
                    ->where('id_producto', $id_producto)
                    ->update([
                        'id_producto' => $nuevoIdProducto,
                        'cantidad' => $cantidadSolicitada,
                    ]);
            }
        }

        return redirect()->route('carro.index')->with('success', 'Carro actualizado correctamente.');
    }

    public function agregarMultiples(Request $request)
    {
        $userId = $request->input('id_user');
        $idPedido = $request->input('id_pedido');
        $seleccionados = $request->input('productos_seleccionados', []);
        $cantidades = $request->input('cantidades', []);

    // Verifica si el pedido está cerrado
        if ($idPedido) {
            $pedido = Pedido::find($idPedido);
            if ($pedido && $pedido->estado_pedido == 0) {
                return redirect()->back()->with('error', 'No se puede agregar productos a un pedido cerrado.');
            }
        }

        if (empty($seleccionados)) {
            return back()->with('error', 'No seleccionaste ningún producto.');
        }

        // Crear o usar carro del pedido
        $carro = Carro::firstOrCreate([
            'id_user' => $userId,
            'id_pedido' => $idPedido,
        ]);

        foreach ($seleccionados as $idProducto) {
        $producto = Producto::find($idProducto);
        if (!$producto) continue;

        $cantidad = isset($cantidades[$idProducto]) ? (int)$cantidades[$idProducto] : 0;
        if ($cantidad <= 0) continue;

        $reservadas = DB::table('carro_productos')
            ->where('id_producto', $idProducto)
            ->whereIn('id_carro', Carro::where('id_pedido', $idPedido)->pluck('id_carro'))
            ->sum('cantidad');

        $disponibles = max(0, $producto->piezas - $reservadas);

        if ($cantidad > $disponibles) {
            return back()->with('error', 'No hay suficientes piezas de "' . $producto->nombre . '". Solo quedan ' . $disponibles);
        }

        $carrosDelPedido = Carro::where('id_pedido', $idPedido)->get();
        $productoYaExiste = false;

        foreach ($carrosDelPedido as $carrito) {
            $productoEnCarro = $carrito->productos()->where('productos.id_producto', $idProducto)->first();
            if ($productoEnCarro) {
                $cantidadActual = $productoEnCarro->pivot->cantidad;
                $carrito->productos()->updateExistingPivot($idProducto, [
                    'cantidad' => $cantidadActual + $cantidad
                ]);
                $productoYaExiste = true;
                break;
            }
        }

        if (!$productoYaExiste) {
            $carro->productos()->attach($idProducto, ['cantidad' => $cantidad]);
        }
    }

        return redirect('/carro')->with('success', 'Productos agregados al carrito.');
    }

    public function show(Request $request)
    {
        $id = $request->input('id_carro');
        $carro = Carro::find($id);
        if (!$carro) {
            return redirect()->back()->with('error', 'El carro no se encontró.');
        }
        return view('/carro/showCarro', ['carro' => $carro]);
    }

    public function eliminarProducto($id_carro, $id_producto)
    {
        $carro = Carro::findOrFail($id_carro);

        // Eliminar solo la relación con el producto
        $carro->productos()->detach($id_producto);

        // (Opcional) Si el carro ya no tiene productos, puedes eliminarlo
        if ($carro->productos()->count() == 0) {
            $carro->delete();
        }

        return redirect()->route('carro.index')->with('success', 'Producto eliminado del carrito.');
    }

    public function destroy(Carro $carro)
    {
        $carro->productos()->detach();
        $carro->delete();

        return redirect()->route('carro.index')->with('success', 'Carro eliminado.');
    }
}
