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

        $productoIndex = \App\Models\Producto::all();

        return view('producto.productoIndex', compact('productoIndex', 'pedidosUsuario'));
    }

    public function create()
    {
        return view('producto.createProducto');
    }

    public function store(Request $request)
    {
        $producto = new Producto();
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

    public function show($id)
    {
        $producto = Producto::find($id);
        if (!$producto) {
            return back()->with('error', 'Producto no encontrado.');
        }

        return view('producto.showProducto', compact('producto'));
    }

    public function edit($id)
    {
        $producto = Producto::find($id);
        if (!$producto) {
            return back()->with('error', 'Producto no encontrado.');
        }

        return view('producto.editProducto', compact('producto'));
    }

    public function update(Request $request, $id)
    {
        $producto = Producto::find($id);
        if (!$producto) {
            return redirect()->route('producto.index')->with('error', 'Producto no encontrado.');
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
