<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\Pedido;
use App\Models\Cliente;
use Illuminate\Http\Request;

class CompraController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $compra = new Compra ();

        $compraIndex = Compra::all();
        return view('compra/compraIndex', compact ('compraIndex'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('compra/createCompra');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_pedido' => 'required|integer|unique:pedidos,id_pedido',
            'nombre_usuario' => 'required|string|unique:clientes,nombre_usuario',
            'estado_compra' => 'required|boolean',
        ], [
            'id_pedido.required' => 'Debe seleccionar un pedido.',
            'id_pedido.integer' => 'El ID del pedido debe ser un número entero.',
            'id_pedido.unique' => 'El ID del pedido debe ser único.',
            'nombre_usuario.required' => 'Debe seleccionar un cliente para la compra.',
            'nombre_usuario.string' => 'El nombre de usuario debe ser una cadena de texto.',
            'nombre_usuario.unique' => 'El nombre del usuario seleccionado debe ser único.',
            'estado_compra.required' => 'Debe seleccionar un estado de la compra.',
            'estado_compra.boolean' => 'El estado debe ser activo o desactivo.', 
        ]);
        $compra = new Compra();
        // Revisar el id compraproducto
        $compra->id_pedido = $idPedido;
        $compra->nombre_usuario = $$nombre_usuario;
        $compra->estado_compra = $request->estado_compra;
        
        if ($compra->save()) {
            return redirect('/compra')->with('success', 'Compra registrado correctamente.');
        } else {
            return redirect()->back()->withErrors(['Error al guardar la compra. Por favor, intenta de nuevo.']);
        } 
    }

    /**
     * Display the specified resource.
     */
    public function show(Compra $compra)
    {
        $id = $request->input('id_compra');
        $compra = Compra::find($compra);
            
        if (!$compra) {
            return redirect()->back()->with('error', 'La compra no se encontró.');
        }
        return view('/compra/showCompra', ['compra' => $compra]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Compra $compra)
    {
        $compra = Compra::find($id);
        $pedido = Pedido::all();
        $cliente = Cliente::all();

        if (!$compra) {
            return redirect()->back()->with('error', 'La compra no se encontró.');
                // return redirect()->route('/producto/productoIndex')->with('error', 'El producto no se encontró.');
        }
        return view('/compra/editCompra', ['compra' => $compra]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Compra $compra)
    {
        $request->validate([
            'id_pedido' => 'required|integer|unique:pedidos,id_pedido',
            'nombre_usuario' => 'required|string|unique:clientes,nombre_usuario',
            'estado_compra' => 'required|boolean',
        ], [
            'id_pedido.required' => 'Debe seleccionar un pedido.',
            'id_pedido.integer' => 'El ID del pedido debe ser un número entero.',
            'id_pedido.unique' => 'El ID del pedido debe ser único.',
            'nombre_usuario.required' => 'Debe seleccionar un cliente para la compra.',
            'nombre_usuario.string' => 'El nombre de usuario debe ser una cadena de texto.',
            'nombre_usuario.unique' => 'El nombre del usuario seleccionado debe ser único.',
            'estado_compra.required' => 'Debe seleccionar un estado de la compra.',
            'estado_compra.boolean' => 'El estado debe ser activo o desactivo.',            
        ]);
        $compra = Compra::find($id);
    
        if (!$compra) {
            return redirect()->route('compra.compraIndex')->with('error', 'La compra no se encontró.');
        }
        $compra->id_pedido = $idPedido;
        $compra->nombre_usuario = $nombre_usuario;
        $compra->estado_compra = $request->estado_compra;
        $compra->save();
        return redirect()->route('compra.compraIndex')->with('success', 'La compra se ha actualizado con éxito.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Compra $compra)
    {    
        $compra = Compra::find($id);

        // if ($aceite->archivo_ubicacion) {
        //     Storage::delete($aceite->archivo_ubicacion);
        // }

        if (!$compra) {
            return redirect()->route('compra.compraIndex')->with('error', 'La compra no se encontró.');
        }

        $compra->delete();

        return redirect()->route('compra.compraIndex')->with('success', 'La compra se ha eliminado con éxito.');
    }
}
