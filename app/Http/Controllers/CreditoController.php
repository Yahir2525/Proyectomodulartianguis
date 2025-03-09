<?php

namespace App\Http\Controllers;

use App\Models\Credito;
use App\Models\Cliente;
use App\Models\Compra;
use Illuminate\Http\Request;

class CreditoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $credito = new Credito ();

        $creditoIndex = Credito::all();
        return view('credito/creditoIndex', compact ('creditoIndex'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('credito/createCredito');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre_usuario' => 'required|string|unique:clientes,nombre_usuario',
            'id_compra' => 'required|integer|unique:compras,id_compra',
            'fecha_liquidacion' => 'required|date|',
            'fecha_vencimiento' => 'required|date|',
            'estado' => 'required|boolean',
            'saldo_pendiente' => 'required|numeric',
        ], [
            'nombre_usuario.required' => 'Debe seleccionar el cliente que solicita el credito.',
            'nombre_usuario.string' => 'El nombre de usuario debe ser una cadena de texto',
            'nombre_usuario.unique' => 'El nombre del usuario seleccionado debe ser único.',
            'id_compra.required' => 'Debe seleccionar una compra .',
            'id_compra.integer' => 'El ID de la compra debe ser un número entero.',
            'id_compra.unique' => 'El ID de la compra debe ser único.',
            'fecha_liquidacion.required' => 'Es requerida una fecha de liquidación.',
            'fecha_liquidacion.date' => 'Este dato debe ser una fecha.',
            'fecha_vencimiento.required' => 'Es requerida una fecha de vencimiento.',
            'fecha_vencimiento.date ' => 'Este dato debe ser una fecha.',
            'estado.required' => 'Es requerido un estado del credito.',
            'estado.boolean' => 'El estado debe ser activo o desactivo.',
            'saldo_pendiente.required' => 'El saldo pendiente es obligatorio.',
            'saldo_pendiente.numeric' => 'El saldo pendiente debe ser un número.',
        ]);
        $credito = new Credito();
        $credito->nombre_usuario = $nombre_usuario;
        $credito->id_compra = $idCompra;
        $credito->fecha_liquidacion = $request->fecha_liquidacion;
        $credito->fecha_vencimiento = $request->fecha_vencimiento;
        $credito->estado = $request->estado;
        $credito->saldo_pendiente = $request->saldo_pendiente;


        if ($credito->save()) {
            return redirect('/credito')->with('success', 'Credito registrado correctamente.');
        } else {
            return redirect()->back()->withErrors(['Error al guardar el credito. Por favor, intenta de nuevo.']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        $id = $request->input('id_credito');
        $credito = Credito::find($credito);
            
        if (!$credito) {
            return redirect()->back()->with('error', 'El credito no se encontró.');
        }
        return view('/credito/showCredito', ['credito' => $credito]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $credito = Credito::find($id);
        // $cliente = Cliente::all();
        // $compra = Compra::all();

        if (!$credito) {
            return redirect()->back()->with('error', 'El credito no se encontró.');
                // return redirect()->route('/producto/productoIndex')->with('error', 'El producto no se encontró.');
        }
        return view('/credito/editCredito', ['credito' => $credito]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Credito $credito)
    {
        $request->validate([
            'nombre_usuario' => 'required|string|unique:clientes,nombre_usuario',
            'id_compra' => 'required|integer|unique:compras,id_compra',
            'fecha_liquidacion' => 'required|date|',
            'fecha_vencimiento' => 'required|date|',
            'estado' => 'required|boolean',
            'saldo_pendiente' => 'required|numeric',
        ], [
            'nombre_usuario.required' => 'Debe seleccionar el cliente que solicita el credito.',
            'nombre_usuario.string' => 'El nombre de usuario debe ser una cadena de texto',
            'nombre_usuario.unique' => 'El nombre del usuario seleccionado debe ser único.',
            'id_compra.required' => 'Debe seleccionar una compra .',
            'id_compra.integer' => 'El ID de la compra debe ser un número entero.',
            'id_compra.unique' => 'El ID de la compra debe ser único.',
            'fecha_liquidacion.required' => 'Es requerida una fecha de liquidación.',
            'fecha_liquidacion.date' => 'Este dato debe ser una fecha.',
            'fecha_vencimiento.required' => 'Es requerida una fecha de vencimiento.',
            'fecha_vencimiento.date ' => 'Este dato debe ser una fecha.',
            'estado.required' => 'Es requerido un estado del credito.',
            'estado.boolean' => 'El estado debe ser activo o desactivo.',
            'saldo_pendiente.required' => 'El saldo pendiente es obligatorio.',
            'saldo_pendiente.numeric' => 'El saldo pendiente debe ser un número.',
        ]);
        $credito = Credito::find($id);
        
    
        if (!$credito) {
            return redirect()->route('credito.creditoIndex')->with('error', 'El credito no se encontró.');
        }
        $credito->nombre_usuario = $nombre_usuario;
        $credito->id_compra = $idCompra;
        $credito->fecha_liquidacion = $request->fecha_liquidacion;
        $credito->fecha_vencimiento = $request->fecha_vencimiento;
        $credito->estado = $request->estado;
        $credito->saldo_pendiente = $request->saldo_pendiente;
        $credito->save();
        return redirect()->route('credito.creditoIndex')->with('success', 'El credito se ha actualizado con éxito.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $credito = Credito::find($id);

        // if ($aceite->archivo_ubicacion) {
        //     Storage::delete($aceite->archivo_ubicacion);
        // }

        if (!$credito) {
            return redirect()->route('credito.index')->with('error', 'El credito no se encontró.');
        }

        $credito->delete();

        return redirect()->route('credito.index')->with('success', 'El credito se ha eliminado con éxito.');
    }
}
