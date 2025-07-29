<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductoController extends Controller
{
    public function index()
    {
        $usuario = Auth::user();

        $usuarios = collect();
        $pedidosUsuario = collect();

        if ($usuario->hasRole('administrador')) {
            $usuarios = \App\Models\User::all();
            $pedidosUsuario = \App\Models\Pedido::with('user')->get(); // Todos los pedidos
        } else {
            $pedidosUsuario = \App\Models\Pedido::where('id_user', $usuario->id_user)->get();
        }

        $productoIndex = Producto::all();

        return view('producto.productoIndex', compact('productoIndex', 'pedidosUsuario', 'usuarios'));
    }


    public function create()
    {
        $tiposExistentes = Producto::select('tipo')->distinct()->pluck('tipo');
        $materialesExistentes = Producto::select('material')->distinct()->pluck('material');
        $coloresExistentes = Producto::select('color')->distinct()->pluck('color');
        $tamaniosExistentes = Producto::select('tamanio')->distinct()->pluck('tamanio');
        $marcasExistentes = Producto::select('marca')->distinct()->pluck('marca');

        return view('producto.createProducto', compact(
            'tiposExistentes',
            'materialesExistentes',
            'coloresExistentes',
            'tamaniosExistentes',
            'marcasExistentes'
        ));
    }

    public function store(Request $request)
    {
        $producto = new Producto();

        // Imagen (si se subió)
        if ($request->hasFile('imagen')) {
            $archivo = $request->file('imagen');
            $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
            $archivo->move(public_path('img'), $nombreArchivo);
            $producto->imagen = 'img/' . $nombreArchivo;
        }

        // Campos normales
        $producto->nombre = $request->input('nombre');
        $producto->tipo = $request->input('tipo');
        $producto->material = $request->input('material');
        $producto->color = $request->input('color');
        $producto->tamanio = $request->input('tamanio');
        $producto->marca = $request->input('marca');
        $producto->precio_unitario = $request->input('precio_unitario');
        $producto->piezas = $request->input('piezas');
        $producto->save();

        return redirect('/producto')->with('success', 'Producto registrado correctamente.');
    }

    public function show(Request $request)
    {
        $busqueda = $request->input('busqueda');

        $productos = collect();
        if (is_numeric($busqueda)) {
            $producto = \App\Models\Producto::find($busqueda);
            if ($producto) {
                $productos->push($producto);
            }
        } elseif ($busqueda) {
            $productos = \App\Models\Producto::where('nombre', 'ILIKE', "%$busqueda%")->get();
        }

        $usuarios = \App\Models\User::all();
        $pedidosUsuario = \App\Models\Pedido::with('user')->get();

        return view('producto.showProducto', compact('productos', 'usuarios', 'pedidosUsuario'));
    }






    public function edit($id)
    {
        $producto = Producto::find($id);
        if (!$producto) {
            return back()->with('error', 'Producto no encontrado.');
        }

        $tiposExistentes = Producto::select('tipo')->distinct()->pluck('tipo');
        $materialesExistentes = Producto::select('material')->distinct()->pluck('material');
        $coloresExistentes = Producto::select('color')->distinct()->pluck('color');
        $tamaniosExistentes = Producto::select('tamanio')->distinct()->pluck('tamanio');
        $marcasExistentes = Producto::select('marca')->distinct()->pluck('marca');

        return view('producto.editProducto', compact(
            'producto',
            'tiposExistentes',
            'materialesExistentes',
            'coloresExistentes',
            'tamaniosExistentes',
            'marcasExistentes'
        ));
    }

    public function update(Request $request, $id)
    {
        $producto = Producto::find($id);
        if (!$producto) {
            return redirect()->route('producto.index')->with('error', 'Producto no encontrado.');
        }

        // Si subieron una imagen, la actualizamos
        if ($request->hasFile('imagen')) {
            $archivo = $request->file('imagen');
            $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
            $archivo->move(public_path('img'), $nombreArchivo);

            if ($producto->imagen && file_exists(public_path($producto->imagen))) {
                unlink(public_path($producto->imagen));
            }

            $producto->imagen = 'img/' . $nombreArchivo;
        }

        // Solo actualizar campos si están presentes y no vacíos
        if ($request->filled('nombre')) {
            $producto->nombre = $request->input('nombre');
        }
        if ($request->filled('tipo')) {
            $producto->tipo = $request->input('tipo');
        }
        if ($request->filled('material')) {
            $producto->material = $request->input('material');
        }
        if ($request->filled('color')) {
            $producto->color = $request->input('color');
        }
        if ($request->filled('tamanio')) {
            $producto->tamanio = $request->input('tamanio');
        }
        if ($request->filled('marca')) {
            $producto->marca = $request->input('marca');
        }
        if ($request->filled('precio_unitario')) {
            $producto->precio_unitario = $request->input('precio_unitario');
        }
        if ($request->filled('piezas')) {
            $producto->piezas = $request->input('piezas');
        }

        $producto->save();

        return redirect()->route('producto.index')->with('success', 'Producto actualizado correctamente.');
    }


    public function destroy($id)
    {
        $producto = Producto::find($id);
        if (!$producto) {
            return redirect()->route('producto.index')->with('error', 'Producto no encontrado.');
        }

        $producto->delete();
        return redirect()->route('producto.index')->with('success', 'Producto eliminado.');
    }
}
