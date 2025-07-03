<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Abono;
use App\Models\Cliente;
use App\Models\Carro;
use App\Models\Credito;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\DetallePedido;
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
        $creditoIndex = Credito::where('id_user', $userId)->get();
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
        $credito = new Credito();
        $credito->id_user = $request->input('id_user');
        $credito->fecha_liquidacion = $request->fecha_liquidacion;
        $credito->fecha_vencimiento = $request->fecha_vencimiento;
        $credito->estado = $request->estado;


        if ($credito->save()) {
            return redirect('/credito')->with('success', 'Credito registrado correctamente.');
        } else {
            return redirect()->back()->withErrors(['Error al guardar el credito. Por favor, intenta de nuevo.']);
        }
    }

    public function crearDesdePedido(Request $request, Pedido $pedido)
    {
        $pedido = Pedido::find($pedido->id_pedido);
        if (!$pedido) {
            return back()->with('error', 'Pedido no encontrado.');
        }

        if ($pedido->id_credito) {
            return back()->with('error', 'Este pedido ya tiene un crédito asignado.');
        }

        $credito = new Credito();
        $credito->id_user = $request->input('id_user');
        if ($request->has('total')) {
        $credito->saldo_total = $request->input('total');}
        $credito->fecha_liquidacion = $request->input('fecha_liquidacion') ?? now();
        $credito->fecha_vencimiento = $request->input('fecha_vencimiento') ?? now()->addDays(30);
        $credito->estado = 1;
        $credito->save();

        // Asignar crédito al pedido
        $pedido->id_credito = $credito->id_credito;
        $pedido->save();

        return redirect('/credito')->with('success', 'Credito registrado correctamente.');
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
        $credito = Credito::find($credito->id_credito);
        
        if (!$credito) {
            return redirect()->route('credito.index')->with('error', 'El credito no se encontró.');
        }
        if ($request->has('total')) {
        $credito->saldo_total = $request->input('total');}
        
        $credito->save();
        return redirect()->route('credito.index')->with('success', 'El credito se ha actualizado con éxito.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $credito = Credito::find($id);

        if (!$credito) {
            return redirect()->route('credito.index')->with('error', 'El credito no se encontró.');
        }

        $abonos = Abono::where('id_user', $id)->get();

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
