<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $producto = new Producto();
        $productoIndex = Producto::all();
        return view('producto/productoIndex', compact ('productoIndex'));
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('producto/createProducto');
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'material' => 'requiered|string|max:20',
            'color'=> 'requiered|string|max:20',
            'tamanio' => 'required|string|max:25',
            'marca' => 'required|string|max:25',
            'precio_unitario' => 'required|decimal|max:30',
            'piezas' => 'required|integer',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.string' => 'El nombre debe ser una cadena de texto.',
            'nombre.max' => 'El nombre no puede tener más de 100 caracteres.',
            'material.required' => 'El material es obligatorio.',
            'material.string' => 'El material debe ser una cadena de texto.',
            'material.max' => 'El material no puede tener mas de 20 caracteres.',
            'color.required' => 'El color es obligatorio.',
            'color.string' => 'El color debe ser una cadena de caracteres.',
            'color.max' => 'El color no puede tener mas de 20 caracteres.',
            'tamanio.required' => 'El tamaño es obligatorio.',
            'tamanio.string' => 'El tamaño debe ser una cadena de caracteres.',
            'tamanio.min' => 'El tamaño debe tener maximo 25 caracteres.',
            'marca.required' => 'La marca es obligatoria.',
            'marca.string' => 'La marca debe ser una cadena de caracteres.',
            'marca.max' => 'La marca no puede tener más de 25 caracteres.',
            'precio_unitario.required' => 'El precio es obligatorio.',
            'precio_unitario.string' => 'El precio debe de ser un numero.',
            'precio_unitario' => 'El precio debe de tener maximo 30 caracteres.',
            'piezas.required' => 'El nombre de la pieza es obligatorio.',
            'piezas.integer' => '',
            
        ]);
            // $request->validate([
            //     'direccion' => 'required|string|max:255',
            //     'telefono' => 'required|string|unique:clientes',
            //     'comentario' => 'nullable|string|max:255',
            // ], [
            //     'direccion.required' => 'El campo dirección es obligatorio.',
            //     'direccion.string' => 'El campo dirección debe ser una cadena de texto.',
            //     'direccion.max' => 'El campo dirección no puede tener más de 255 caracteres.',
            //     'telefono.required' => 'El campo teléfono es obligatorio.',
            //     'telefono.string' => 'El campo teléfono debe ser una cadena de texto.',
            //     'telefono.unique' => 'El teléfono ya está en uso.',
            //     'comentario.string' => 'El campo comentario debe ser una cadena de texto.',
            //     'comentario.max' => 'El comentario no puede tener más de 255 caracteres.'
            // ]);
            $producto = new Producto();
            $producto->nombre = $request->nombre;
            $producto->material = $request->material;
            $producto->color = $request->color;
            $producto->tamanio = $request->tamanio;
            $producto->marca = $request->marca;
            $producto->precio_unitario = $request->precio_unitario;
            $producto->piezas = $request->piezas;
            
            $producto->save();
        return redirect('/producto')->with('success', 'Producto registrado correctamente.');
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Producto $producto)
    {
        $id = $request->input('id_producto');
        $producto = Producto::find($id);
            
        if (!$producto) {
            return redirect()->back()->with('error', 'El producto no se encontró.');
        }
        return view('/abono/showProducto', ['producto' => $producto]);
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Producto $producto)
    {
        $producto = Producto::find($id);

            if (!$producto) {
                return redirect()->route('producto.productoIndex')->with('error', 'El producto no se encontró.');
            // }

            return view('/producto/editProducto', ['producto' => $producto]);   
        }
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Producto $producto)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'material' => 'requiered|string|max:20',
            'color'=> 'requiered|string|max:20',
            'tamanio' => 'required|string|max:25',
            'marca' => 'required|string|max:25',
            'precio_unitario' => 'required|decimal|max:30',
            'piezas' => 'required|integer',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.string' => 'El nombre debe ser una cadena de texto.',
            'nombre.max' => 'El nombre no puede tener más de 100 caracteres.',
            'material.required' => 'El material es obligatorio.',
            'material.string' => 'El material debe ser una cadena de texto.',
            'material.max' => 'El material no puede tener mas de 20 caracteres.',
            'color.required' => 'El color es obligatorio.',
            'color.string' => 'El color debe ser una cadena de caracteres.',
            'color.max' => 'El color no puede tener mas de 20 caracteres.',
            'tamanio.required' => 'El tamaño es obligatorio.',
            'tamanio.string' => 'El tamaño debe ser una cadena de caracteres.',
            'tamanio.min' => 'El tamaño debe tener maximo 25 caracteres.',
            'marca.required' => 'La marca es obligatoria.',
            'marca.string' => 'La marca debe ser una cadena de caracteres.',
            'marca.max' => 'La marca no puede tener más de 25 caracteres.',
            'precio_unitario.required' => 'El precio es obligatorio.',
            'precio_unitario.string' => 'El precio debe de ser un numero.',
            'precio_unitario' => 'El precio debe de tener maximo 30 caracteres.',
            'piezas.required' => 'El nombre de la pieza es obligatorio.',
            'piezas.integer' => '',
            
        ]);
        $producto = Producto::find($id);
    
        if (!$producto) {
            return redirect()->route('producto.productoIndex')->with('error', 'El producto no se encontró.');
        }
        $producto = new Producto();
        $producto->nombre = $request->nombre;
        $producto->material = $request->material;
        $producto->color = $request->color;
        $producto->tamanio = $request->tamanio;
        $producto->marca = $request->marca;
        $producto->precio_unitario = $request->precio_unitario;
        $producto->piezas = $request->piezas;
        $producto->save();
        return redirect()->route('producto.productoIndex')->with('success', 'El producto se ha actualizado con éxito.');
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Producto $producto)
    {
        $producto = Producto::find($id);


        if (!$producto) {
            return redirect()->route('producto.productoIndex')->with('error', 'El producto no se encontró.');
        }
        

        $cliente->delete();

        return redirect()->route('producto.productoIndex')->with('success', 'El producto se ha eliminado con éxito.');
        //
    }
}
