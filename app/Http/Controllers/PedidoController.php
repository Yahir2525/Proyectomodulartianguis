<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Illuminate\Http\Request;

class PedidoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pedido = new Pedido ();

        $pedidoIndex = Pedido::all();
        return view('pedido/pedidoIndex', compact ('pedidoIndex'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pedido/createPedido');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'id_compra' => 'required|integer|unique:compras,id_compra',
            // 'nombre_usuario' => 'required|string|unique:clientes,nombre_usuario',
        ], [
            'id_compra.required' => 'Debes una elegir una selección de productos .',
            'id_compra.integer' => 'El ID de la compra-producto debe ser un número entero.',
            'id_compra.unique' => 'El ID debe ser único.',
            // 'nombre_usuario.required' => 'Debes seleccionar un cliente para la compra.',
            // 'nombre_usuario.string' => 'El nombre de usuario debe ser una palabra compuesta.',
            // 'nombre_usuario.exists' => 'El nombre del usuario seleccionado debe ser único.',
        ]);
        $abono = new Aceite();
        $abono->monto_abono = $request->monto_abono;
        
        if ($abono->save()) {
            return redirect('/abono')->with('success', 'Abono registrado correctamente.');
        } else {
            return redirect()->back()->withErrors(['Error al guardar el abono. Por favor, intenta de nuevo.']);
        } 
    
    }

    /**
     * Display the specified resource.
     */
    public function show(Pedido $pedido)
    {
        $id = $request->input('id_pedido');
        $pedido = Pedido::find($compra);
            
        if (!$pedido) {
            return redirect()->back()->with('error', 'La pedido no se encontró.');
        }
        return view('/pedido/showPedido', ['pedido' => $pedido]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pedido $pedido)
    {
        $pedido = Pedido::find($id);

            if (!$pedido) {
                return redirect()->route('pedido.pedidoIndex')->with('error', 'La pedido no se encontró.');
            // }

            return view('/pedido/editPedido', ['pedido' => $pedido]);   
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pedido $pedido)
    {
        $request->validate([
            'id_compra' => 'required|integer|unique:compras,id_compra',
            // 'nombre_usuario' => 'required|string|unique:clientes,nombre_usuario',
        ], [
            'id_compra.required' => 'Debes una elegir una selección de productos .',
            'id_compra.integer' => 'El ID de la compra-producto debe ser un número entero.',
            'id_compra.unique' => 'El ID debe ser único.',
            'nombre_usuario.required' => 'Debes seleccionar un cliente para la compra.',
            // 'nombre_usuario.string' => 'El nombre de usuario debe ser una palabra compuesta.',
            // 'nombre_usuario.exists' => 'El nombre del usuario seleccionado debe ser único.',
        ]);
        $pedido = Pedido::find($id);
    
        if (!$pedido) {
            return redirect()->route('pedido.pedidoIndex')->with('error', 'La pedido no se encontró.');
        }
        // $compra->estado_compra = $request->estado_compra;
        // $compra->nombre_usuario = $request->nombre_usuario;
        $pedido->save();
        return redirect()->route('pedido.pedidoIndex')->with('success', 'La pedido se ha actualizado con éxito.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pedido $pedido)
    {
        //
    }
}
