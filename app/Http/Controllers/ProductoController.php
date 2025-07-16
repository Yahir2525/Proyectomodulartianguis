<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductoController extends Controller
{
    public function index()
    {
        $usuarioId = Auth::id();
        $pedidosUsuario = \App\Models\Pedido::where('id_user', $usuarioId)->get();
        $productoIndex = Producto::all();

        return view('producto.productoIndex', compact('productoIndex', 'pedidosUsuario'));
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
        $id = $request->input('id_producto');
        $producto = Producto::find($id);
        if (!$producto) {
            return redirect()->back()->with('error', 'El producto no se encontró.');
        }
        return view('producto.showProducto', ['producto' => $producto]);
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

        if ($request->hasFile('imagen')) {
            $archivo = $request->file('imagen');
            $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
            $archivo->move(public_path('img'), $nombreArchivo);

            if ($producto->imagen && file_exists(public_path($producto->imagen))) {
                unlink(public_path($producto->imagen));
            }

            $producto->imagen = 'img/' . $nombreArchivo;
        }

        $producto->nombre = $request->input('nombre');
        $producto->tipo = $request->input('tipo');
        $producto->material = $request->input('material');
        $producto->color = $request->input('color');
        $producto->tamanio = $request->input('tamanio');
        $producto->marca = $request->input('marca');
        $producto->precio_unitario = $request->input('precio_unitario');
        $producto->piezas = $request->input('piezas');
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
