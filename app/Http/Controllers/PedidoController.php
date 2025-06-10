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

class PedidoController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $pedido = new Pedido ();

        $pedidoIndex = Pedido::all();
        $creditos = Credito::all()->groupBy('id_user'); // Agrupar por usuario
        return view('pedido/pedidoIndex', compact ('pedidoIndex','creditos'));
    }

    public function create()
    {
        return view('pedido/createPedido');
    }

    public function store(Request $request)
    {
        $pedido = new Pedido();
        $pedido->id_user = $request->input('id_user');
        $pedido->id_credito = $request->input('id_credito');
        
        $pedido->estado_pedido = 1;
        if ($pedido->save()) {
            return redirect('/pedido')->with('success', 'Pedido registrado correctamente.');
        }
    }

    public function show(Request $request)
    {
        $id = $request->input('id_pedido');
        $pedido = Pedido::find($id);
        if (!$pedido) {
            return redirect()->back()->with('error', 'El pedido no se encontró.');
        }
        return view('/pedido/showPedido', ['pedido' => $pedido]);
    }

    public function edit($id)
    {
        $pedido = Pedido::find($id);
        return view('/pedido/editPedido', ['pedido' => $pedido]);
    }

    public function update(Request $request, Pedido $pedido)
    {
        $pedido = Pedido::find($pedido->id_pedido);
    
        if (!$pedido) {
            return redirect()->route('pedido.index')->with('error', 'El pedido no se encontró.');
        }
        $pedido->id_credito = $request->input('id_credito');
        if ($request->has('total')) {
        $pedido->total_pedido = $request->input('total');}
        // $pedido->estado_pedido = $request->input('estado_pedido');
        $pedido->save();
        return redirect()->route('pedido.index')->with('success', 'El pedido se ha actualizado con éxito.');
    }

    public function destroy(Pedido $pedido)
    {
        $pedido = Pedido::find($pedido->id_pedido);
        
        if (!$pedido) {
            return redirect()->route('pedido.index')->with('error', 'El pedido no se encontró.');
        }
        $pedido->delete();

        return redirect()->route('pedido.index')->with('success', 'El pedido se ha eliminado con éxito.');
    }
}
