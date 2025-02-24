<?php

namespace App\Http\Controllers;

use App\Models\Vendedor;
use Illuminate\Http\Request;

class VendedorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vendedor = new Vendedor();
        $vendedorIndex = Vendedor::all();
        return view('vendedor/vendedorIndex', compact ('vendedorIndedx'));
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('vendedor/createVendedor');
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'genero' => 'requiered|character|min:1',
            'edad'=> 'requiered|integer|min:0|max:120',
            'telefono' => 'required|integer|min:10|unique:vendedors',
            'direccion' => 'required|string|max:255',
            'correo' => 'required|string|email|max:30|unique:vendedors',
            'nombre_usuario' => 'required|string|max:30|unique:vendedors',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.string' => 'El nombre debe ser una cadena de texto.',
            'nombre.max' => 'El nombre no puede tener más de 100 caracteres.',
            'genero.required' => 'El genero es obligatorio.',
            'genero.character' => 'El genero debe ser un caracter.',
            'genero.min' => 'El genero debe ser al menos 1.',
            'edad.required' => 'La edad es obligatoria.',
            'edad.integer' => 'La edad debe ser un entero.',
            'edad.min' => 'La edad debe ser minimo 0.',
            'edad.max' => 'La edad debe ser máximo 120 años.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'telefono.integer' => 'El teléfono debe ser un entero.',
            'telefono.min' => 'El teléfono debe tener al menos diez digitos.',
            'telefono.unique' => 'El teléfono ya está en uso.',
            'direccion.required' => 'La dirección es obligatorio.',
            'direccion.string' => 'La dirección debe ser una cadena de texto.',
            'direccion.max' => 'La dirección no puede tener más de 255 caracteres.',
            'correo.required' => 'El correo electrónico es obligatorio.',
            'correo.string' => 'El correo electrónico debe ser una cadena de texto.',
            'correo.email' => 'El correo electrónico no es válido.',
            'correo.unique' => 'El correo electrónico ya está en uso.',
            'correo.max' => 'El correo electrónico no puede tener más de 30 caracteres.',
            'nombre_usuario.required' => 'El nombre de usuario es obligatorio.',
            'nombre_usuario.string' => 'El nombre de usuario debe ser una cadena de texto.',
            'nombre_usuario.max' => 'El nombre de usuario no puede tener más de 30 caracteres.',
            'nombre_usuario.unique' => 'El nombre de usuario ya está en uso.',
        ]);
            $vendedor = new Cliente();
            $vendedor->nombre = $request->nombre;
            $vendedor->genero = $request->genero;
            $vendedor->edad = $request->edad;
            $vendedor->telefono = $request->telefono;
            $vendeor->direccion = $request->direccion;
            $vendedor->correo = $request->correo;
            $vendedor->nombre_usuario = $request->nombre_usuario;
            
            $vendedor->save();
            return redirect('/vendedor')->with('success', 'Vendedor registrado correctamente.');
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Vendedor $vendedor)
    {
        $id = $request->input('id_vendedor');
        $cliente = Cliente::find($id);
            
        if (!$cliente) {
            return redirect()->back()->with('error', 'El cliente no se encontró.');
        }
        return view('/abono/showCliente', ['cliente' => $cliente]);
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vendedor $vendedor)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vendedor $vendedor)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vendedor $vendedor)
    {
        //
    }
}
