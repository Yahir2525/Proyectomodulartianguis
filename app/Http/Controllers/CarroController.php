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

        if ($request->has('nuevo_pedido')) {
            $pedido = new Pedido();
            $pedido->id_user = $userId;
            $pedido->id_credito = $request->input('id_credito');
            $pedido->estado_pedido = 1;
            $pedido->metodo_pago = 'contado';
            $pedido->save();
            $pedidoId = $pedido->id_pedido;
        } else {
            $pedidoId = $request->input('id_pedido');
            if (!$pedidoId) {
                return back()->with('error', 'Debes seleccionar un pedido o crear uno nuevo.');
            }
        }

        // Buscar o crear el carro
        $carro = Carro::firstOrCreate([
            'id_user' => $userId,
            'id_pedido' => $pedidoId,
        ]);

        $productoId = $request->input('id_producto');
        $cantidadSolicitada = (int)$request->input('cantidad');

        if ($cantidadSolicitada <= 0) {
            return back()->with('error', 'La cantidad debe ser mayor a 0.');
        }

        $producto = Producto::find($productoId);
        if (!$producto) {
            return back()->with('error', 'Producto no encontrado.');
        }

        $reservadas = DB::table('carro_productos')
            ->where('id_producto', $productoId)
            ->where('id_carro', '!=', $carro->id_carro)
            ->sum('cantidad');

        $disponibles = max(0, $producto->piezas - $reservadas);

        if ($cantidadSolicitada > $disponibles) {
            return back()->with('error', "Solo quedan $disponibles piezas disponibles.");
        }

        // Guardar en la tabla pivote
        $carro->productos()->syncWithoutDetaching([
            $productoId => ['cantidad' => $cantidadSolicitada]
        ]);

        return redirect('/carro')->with('success', 'Producto agregado al carrito.');
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
        $nuevoIdProducto = $request->input('id_producto');
        $cantidadSolicitada = (int) $request->input('cantidad');

        if ($cantidadSolicitada <= 0) {
            return back()->with('error', 'La cantidad debe ser mayor a 0.');
        }

        // Verificar disponibilidad
        $reservadas = DB::table('carro_productos')
            ->where('id_producto', $nuevoIdProducto)
            ->where('id_carro', '!=', $carro->id_carro)
            ->sum('cantidad');

        $producto = Producto::findOrFail($nuevoIdProducto);
        $disponibles = max(0, $producto->piezas - $reservadas);

        if ($cantidadSolicitada > $disponibles) {
            return back()->with('error', "Solo hay $disponibles piezas disponibles.");
        }

        // Si el producto no cambió, solo actualiza la cantidad
        if ($nuevoIdProducto == $id_producto) {
            DB::table('carro_productos')
                ->where('id_carro', $carro->id_carro)
                ->where('id_producto', $id_producto)
                ->update(['cantidad' => $cantidadSolicitada]);
        } else {
            // Verificar que no exista ya (carro, nuevoIdProducto)
            $yaExiste = DB::table('carro_productos')
                ->where('id_carro', $carro->id_carro)
                ->where('id_producto', $nuevoIdProducto)
                ->exists();

            if ($yaExiste) {
                return back()->with('error', 'Ese producto ya está en el carrito. No se puede cambiar.');
            }

            // Actualizar la fila existente: cambiar id_producto y cantidad
            DB::table('carro_productos')
                ->where('id_carro', $carro->id_carro)
                ->where('id_producto', $id_producto)
                ->update([
                    'id_producto' => $nuevoIdProducto,
                    'cantidad' => $cantidadSolicitada,
                ]);
        }

        // Actualizar pedido si fue cambiado
        if ($request->filled('id_pedido')) {
            $carro->id_pedido = $request->input('id_pedido');
            $carro->save();
        }

        return redirect()->route('carro.index')->with('success', 'Producto del carro actualizado.');
    }


    public function agregarMultiples(Request $request)
    {
        $userId = $request->input('id_user');
        $idPedido = $request->input('id_pedido');
        $seleccionados = $request->input('productos_seleccionados', []);
        $cantidades = $request->input('cantidades', []);

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

            // Validar disponibilidad
            $reservadas = DB::table('carro_productos')
                ->where('id_producto', $idProducto)
                ->sum('cantidad');
            $disponibles = max(0, $producto->piezas - $reservadas);

            if ($cantidad > $disponibles) {
                return back()->with('error', 'No hay suficientes piezas de "' . $producto->nombre . '". Solo quedan ' . $disponibles);
            }

            // Verificar si ya está en el carro
            $yaExiste = $carro->productos()->wherePivot('id_producto', $idProducto)->exists();
            if ($yaExiste) {
                // Puedes actualizar la cantidad si quieres:
                $cantidadActual = $carro->productos()->where('id_producto', $idProducto)->first()->pivot->cantidad;
                $carro->productos()->updateExistingPivot($idProducto, [
                    'cantidad' => $cantidadActual + $cantidad
                ]);
            } else {
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

    public function destroy(Carro $carro)
    {
        $carro->productos()->detach();
        $carro->delete();

        return redirect()->route('carro.index')->with('success', 'Carro eliminado.');
    }
}
