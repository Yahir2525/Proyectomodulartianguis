<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\DetallePedido;
use App\Models\Abono;
use App\Models\Cliente;
use App\Models\Compra;
use App\Models\Credito;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Vendedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DetallePedidoController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $detallePedido = new DetallePedido ();

        DetallePedido::all();
        $detalleIndex = DetallePedido::where('id_user', $userId)->get();

        return view('detalle/detalleIndex', compact ('detalleIndex'));
    }

    public function create()
    {
        $usuarioId = Auth::id();
        $pedidosUsuario = Pedido::where('id_user', $usuarioId)->get();
        return view('detalle/createDetalle', compact('usuarioId', 'pedidosUsuario'));
    }

    public function store(Request $request)
    {
        $userId = $request->input('id_user');
        
        if ($request->has('nuevo_pedido')) {
            $pedido = new Pedido();
            $pedido->id_user = $request->input('id_user');
            $pedido->id_credito = $request->input('id_credito');
            $pedido->estado_pedido = 1;
            $pedido->save();
            $pedidoId = $pedido->id_pedido;
        } else {
            $pedidoId = $request->input('id_pedido');

            if (!$pedidoId) {
                return redirect()->back()->with('error', 'Debes seleccionar un pedido o crear uno nuevo.');
            }
        }
        $detallePedido = new Carro();
        $detallePedido->id_user = $request->input('id_user');
        $detallePedido->id_pedido = $pedidoId;
        $pedido->estado_carro = 1;
        $detallePedido->save();
        return redirect('/detalle')->with('success', 'Carro agregado al detalle.');
    }

    public function show(Resquest $request)
    {
        $id = $request->input('id_detalle');
        $detallePedido = DetallePedido::find($id);
        if (!$detallePedido) {
            return redirect()->back()->with('error', 'El carro no se encontró.');
        }
        return view('/detalle/showDetalle', ['detallePedido' => $detallePedido]);
    }

    public function edit(DetallePedido $detallePedido)
    {
        $detallePedido = DetallePedido::find($detallePedido->id_detalle);
        $pedidosUsuario = Pedido::where('id_user', auth()->id())->get();
        return view('detalle.editDetalle', compact('detallePedido', 'pedidosUsuario'));
    }

    public function update(Request $request, DetallePedido $detallePedido)
    {
        $detallePedido = DetallePedido::find($detallePedido->id_detalle);
        $detallePedido->id_pedido = $request->input('id_pedido');

        if (!$detallePedido) {
            return redirect()->route('detalle.index')->with('error', 'El detalle no se encontró.');
        }
        if ($request->has('total')) {
        $detallePedido->total_carro = $request->input('total');}

        $detallePedido->save();
        return redirect()->route('detalle.index')->with('success', 'El detalle se ha actualizado con éxito.');
    }

    public function destroy(DetallePedido $detallePedido)
    {
        $detallePedido = DetallePedido::find($detallePedido->id_detalle);
        
        if (!$detallePedido) {
            return redirect()->route('detalle.index')->with('error', 'El detalle no se encontró.');
        }
        $detallePedido->delete();

        return redirect()->route('detalle.index')->with('success', 'El detalle se ha eliminado con éxito.');
    }
}
