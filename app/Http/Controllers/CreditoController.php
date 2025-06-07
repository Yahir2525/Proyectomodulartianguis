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
use Illuminate\Support\Facades\Auth;

class CreditoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $userId = Auth::id();

        $credito = new Credito ();

        Credito::all();
        $creditoIndex = Carro::where('id_user', $userId)->get();
        return view('credito/creditoIndex', compact ('creditoIndex'));
    }
    //Me quedé en que iba a pasar el total del pedido segun el id seleccionado en la lista de credito del pedido
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
        // $request->validate([
        //     'id_compra' => 'required|integer|unique:compras,id_compra',
        //     'nombre_usuario' => 'required|string|unique:clientes,nombre_usuario',
        //     'fecha_liquidacion' => 'required|date|',
        //     'fecha_vencimiento' => 'required|date|',
        //     'estado' => 'required|boolean',
        //     'saldo_total' => 'required|numeric',
        //     'total_abonado' => 'required|numeric',
        //     'saldo_pendiente' => 'required|numeric',
        // ], [
        //     'id_compra.required' => 'Debe seleccionar una compra .',
        //     'id_compra.integer' => 'El ID de la compra debe ser un número entero.',
        //     'id_compra.unique' => 'El ID de la compra debe ser único.',
        //     'nombre_usuario.required' => 'Debe seleccionar el cliente que solicita el credito.',
        //     'nombre_usuario.string' => 'El nombre de usuario debe ser una cadena de texto',
        //     'nombre_usuario.unique' => 'El nombre del usuario seleccionado debe ser único.',
        //     'fecha_liquidacion.required' => 'Es requerida una fecha de liquidación.',
        //     'fecha_liquidacion.date' => 'Este dato debe ser una fecha.',
        //     'fecha_vencimiento.required' => 'Es requerida una fecha de vencimiento.',
        //     'fecha_vencimiento.date ' => 'Este dato debe ser una fecha.',
        //     'estado.required' => 'Es requerido un estado del credito.',
        //     'estado.boolean' => 'El estado debe ser activo o desactivo.',
        //     'saldo_total.required' => 'El saldo total es obligatorio.',
        //     'saldo_total.numeric' => 'El saldo total debe ser un número.',
        //     'total_abonado.required' => 'El total abonado es obligatorio.',
        //     'total_abonado.numeric' => 'El total abonado debe ser un número.',
        //     'saldo_pendiente.required' => 'El saldo pendiente es obligatorio.',
        //     'saldo_pendiente.numeric' => 'El saldo pendiente debe ser un número.',
        // ]);
        $credito = new Credito();
        $credito->id_compra = $idCompra;
        $credito->id_user = $request->input('id_user');
        $credito->fecha_liquidacion = $request->fecha_liquidacion;
        $credito->fecha_vencimiento = $request->fecha_vencimiento;
        $credito->estado = $request->estado;
        $pedido = Pedido::where('id_pedido', $credito->id)->first();
        $abono = Abono::find($request->id_abono);
        $credito->saldo_total = 0;
        if ($pedido) {
            $credito->saldo_total += $pedido->total_pagar;
        }
        
        // $credito->total_abonado = 0;
        // if ($abono) {
        //     $credito->total_abonado += $abono->monto_abono;
        // }

        // $credito->saldo_pendiente = $credito->saldo_total - $credito->total_abonado;

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
        $credito = Credito::find($id);
            
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
        $credito = Credito::find($id);
        
        if (!$credito) {
            return redirect()->route('credito.creditoIndex')->with('error', 'El credito no se encontró.');
        }
        if ($request->has('total')) {
        $credito->saldo_total = $request->input('total');}

        // $credito->saldo_total = 0;
        // if ($pedido) {
        //     $credito->saldo_total += $pedido->total_pagar;
        // }
        
        // $credito->total_abonado = 0;
        // if ($abono) {
        //     $credito->total_abonado += $abono->monto_abono;
        // }

        // // Inicializar saldo_pendiente correctamente
        // $credito->saldo_pendiente = $credito->saldo_total - $credito->total_abonado;

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

        $abonos = Abono::where('id_cliente', $id)->get();

        foreach ($abonos as $item) {
            $abono = Abono::find($item->id_abono);
            if ($abono) {
                $abono->delete();
            }
        }

        $credito->delete();

        return redirect()->route('credito.index')->with('success', 'El credito se ha eliminado con éxito.');
    }
}
