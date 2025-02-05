<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cliente = new Cliente ();

        $clienteIndex = Cliente::all();
        return view('cliente/clienteIndex', compact ('clienteIndex'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('cliente/createCliente');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'genero' => 'requiered|character|min:1',
            'edad'=> 'requiered|integer|min:0|max:100',
            'telefono' => 'required|string|min:10|unique:clientes',
            'direccion' => 'required|string|max:255',
            'correo' => 'required|string|email|max:30|unique:clientes',
            'nombre_usuario' => 'required|string|max:30|unique:clientes',
        ], [
            'nombre.required' => 'El campo nombre es obligatorio.',
            'nombre.string' => 'El campo nombre debe ser una cadena de texto.',
            'nombre.max' => 'El campo nombre no puede tener más de 100 caracteres.',
            'genero.required' => 'El campo genero es obligatorio.',
            'genero.character' => 'El campo genero debe ser un caracter.',
            'genero.min' => 'El genero debe ser al menos 1.',
            'edad.required' => 'El campo edad es obligatorio.',
            'edad.integer' => 'El campo edad debe ser un entero.',
            'edad.min' => 'La edad debe ser minimo 0.',
            'edad.max' => 'La edad debe ser máximo 100.',
            'telefono.required' => 'El campo teléfono es obligatorio.',
            'telefono.string' => 'El campo teléfono debe ser una cadena de texto.',
            'telefono.min' => 'El teléfono debe tener al menos diez digitos.',
            'telefono.unique' => 'El teléfono ya está en uso.',
            'direccion.required' => 'El campo dirección es obligatorio.',
            'direccion.string' => 'El campo dirección debe ser una cadena de texto.',
            'direccion.max' => 'El campo dirección no puede tener más de 255 caracteres.',
            'correo.required' => 'El campo correo es obligatorio.',
            'correo.string' => 'El campo correo debe ser una cadena de texto.',
            'correo.email' => 'El correo electrónico no es válido.',
            'correo.unique' => 'El correo electrónico ya está en uso.',
            'correo.max' => 'El campo correo no puede tener más de 30 caracteres.',
            'nombre_usuario.required' => 'El campo nombre de usuario es obligatorio.',
            'nombre_usuario.string' => 'El campo nombre de usuario debe ser una cadena de texto.',
            'nombre_usuario.unique' => 'El nombre de usuario ya está en uso.',
            'nombre_usuario.max' => 'El campo nombre de usuario no puede tener más de 30 caracteres.',
        ]);
            $request->validate([
                'direccion' => 'required|string|max:255',
                'telefono' => 'required|string|unique:clientes',
                'comentario' => 'nullable|string|max:255',
            ], [
                'direccion.required' => 'El campo dirección es obligatorio.',
                'direccion.string' => 'El campo dirección debe ser una cadena de texto.',
                'direccion.max' => 'El campo dirección no puede tener más de 255 caracteres.',
                'telefono.required' => 'El campo teléfono es obligatorio.',
                'telefono.string' => 'El campo teléfono debe ser una cadena de texto.',
                'telefono.unique' => 'El teléfono ya está en uso.',
                'comentario.string' => 'El campo comentario debe ser una cadena de texto.',
                'comentario.max' => 'El comentario no puede tener más de 255 caracteres.'
            ]);
            $cliente = new Cliente();
            $cliente->nombre = $request->nombre;
            $cliente->genero = $request->genero;
            $cliente->edad = $request->edad;
            $cliente->telefono = $request->telefono;
            $cliente->direccion = $request->direccion;
            $cliente->correo = $request->correo;
            $cliente->nombre_usuario = $request->nombre_usuario;
            
            $cliente->save();
        return redirect('/cliente')->with('success', 'Cliente registrado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Cliente $cliente)
    {
        $id = $request->input('id_cliente');
        $cliente = Cliente::find($id);
            
        if (!$cliente) {
            return redirect()->back()->with('error', 'El cliente no se encontró.');
        }
        return view('/abono/showCliente', ['cliente' => $cliente]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cliente $cliente)
    {
        $cliente = Cliente::find($id);

            if (!$cliente) {
                return redirect()->route('cliente.clienteIndex')->with('error', 'El cliente no se encontró.');
            // }

            return view('/cliente/editCliente', ['cliente' => $cliente]);   
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cliente $cliente)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'genero' => 'requiered|character|min:1',
            'edad'=> 'requiered|integer|min:0|max:100',
            'telefono' => 'required|string|min:10|unique:clientes',
            'direccion' => 'required|string|max:255',
            'correo' => 'required|string|email|max:30|unique:clientes',
            'nombre_usuario' => 'required|string|max:30|unique:clientes',
        ], [
            'nombre.required' => 'El campo nombre es obligatorio.',
            'nombre.string' => 'El campo nombre debe ser una cadena de texto.',
            'nombre.max' => 'El campo nombre no puede tener más de 100 caracteres.',
            'genero.required' => 'El campo genero es obligatorio.',
            'genero.character' => 'El campo genero debe ser un caracter.',
            'genero.min' => 'El genero debe ser al menos 1.',
            'edad.required' => 'El campo edad es obligatorio.',
            'edad.integer' => 'El campo edad debe ser un entero.',
            'edad.min' => 'La edad debe ser minimo 0.',
            'edad.max' => 'La edad debe ser máximo 100.',
            'telefono.required' => 'El campo teléfono es obligatorio.',
            'telefono.string' => 'El campo teléfono debe ser una cadena de texto.',
            'telefono.min' => 'El teléfono debe tener al menos diez digitos.',
            'telefono.unique' => 'El teléfono ya está en uso.',
            'direccion.required' => 'El campo dirección es obligatorio.',
            'direccion.string' => 'El campo dirección debe ser una cadena de texto.',
            'direccion.max' => 'El campo dirección no puede tener más de 255 caracteres.',
            'correo.required' => 'El campo correo es obligatorio.',
            'correo.string' => 'El campo correo debe ser una cadena de texto.',
            'correo.email' => 'El correo electrónico no es válido.',
            'correo.unique' => 'El correo electrónico ya está en uso.',
            'correo.max' => 'El campo correo no puede tener más de 30 caracteres.',
            'nombre_usuario.required' => 'El campo nombre de usuario es obligatorio.',
            'nombre_usuario.string' => 'El campo nombre de usuario debe ser una cadena de texto.',
            'nombre_usuario.unique' => 'El nombre de usuario ya está en uso.',
            'nombre_usuario.max' => 'El campo nombre de usuario no puede tener más de 30 caracteres.',
        ]);
        $cliente = Cliente::find($id);
    
        if (!$cliente) {
            return redirect()->route('cliente.clienteIndex')->with('error', 'El cliente no se encontró.');
        }
        $cliente = new Cliente();
        $cliente->nombre = $request->nombre;
        $cliente->genero = $request->genero;
        $cliente->edad = $request->edad;
        $cliente->telefono = $request->telefono;
        $cliente->direccion = $request->direccion;
        $cliente->correo = $request->correo;
        $cliente->nombre_usuario = $request->nombre_usuario;
        $cliente->save();
        return redirect()->route('cliente.clienteIndex')->with('success', 'El cliente se ha actualizado con éxito.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cliente $cliente)
    {
        $cliente = Cliente::find($id);

        // if ($aceite->archivo_ubicacion) {
        //     Storage::delete($aceite->archivo_ubicacion);
        // }

        if (!$cliente) {
            return redirect()->route('cliente.clienteIndex')->with('error', 'El cliente no se encontró.');
        }
        
        // $detalleCompras = DetalleCompra::where('id_aceite', $id)->get();

        // foreach ($detalleCompras as $detalleCompra) {
        //     $compra = Compras::find($detalleCompra->id_compras);
        //     if ($compra) {
        //         $compra->delete();
        //     }
            
        //     // Eliminar el DetalleCompras
        //     $detalleCompra->delete();
        // }

        $cliente->delete();

        return redirect()->route('cliente.clienteIndex')->with('success', 'El cliente se ha eliminado con éxito.');
    }
}
