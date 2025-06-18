<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Abono;
use App\Models\Cliente;
use App\Models\Compra;
use App\Models\Credito;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Vendedor;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index()
    {
        $producto = new Producto();
        $productoIndex = Producto::all();
        return view('producto/productoIndex', compact ('productoIndex'));
    }

    public function create()
    {
        return view('producto/createProducto');
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

    public function show(Request $request)
    {
        $id = $request->input('id_producto');
        $producto = Producto::find($id);
            
        if (!$producto) {
            return redirect()->back()->with('error', 'El producto no se encontró.');
        }
        return view('/producto/showProducto', ['producto' => $producto]);
        //
    }

    public function edit(Producto $producto)
    {
        $producto = Producto::find($producto->id_producto);
        if (!$producto) {
            return redirect()->back()->with('error', 'El pedido no se encontró.');
        }
        return view('/producto/editProducto', ['producto' => $producto]);   
    }

    public function update(Request $request, Producto $producto)
    {
        $producto = Producto::find($producto->id_producto);
    
        if (!$producto) {
            return redirect()->route('producto.productoIndex')->with('error', 'El producto no se encontró.');
        }
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
        return redirect()->route('producto.index')->with('success', 'El producto se ha actualizado con éxito.');
        //
    }

    public function destroy(Producto $producto)
    {
        $producto = Producto::find($producto->id_producto);

        if (!$producto) {
            return redirect()->route('producto.index')->with('error', 'El producto no se encontró.');
        }
        
        $producto->delete();

        return redirect()->route('producto.index')->with('success', 'El producto se ha eliminado con éxito.');
    }
}
