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
        $detalleIndex = DetallePedido::where('id_user', $userId)->get();
        return view('detalle/detalleIndex', compact('detalleIndex'));
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

        // Crear nuevo pedido si se solicitó
        if ($request->has('nuevo_pedido')) {
            $pedido = new Pedido();
            $pedido->id_user = $userId;
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

        $detallePedido = new DetallePedido(); // ← corregido aquí
        $detallePedido->id_user = $userId;
        $detallePedido->id_pedido = $pedidoId;
        $detallePedido->estado_carro = 1; // ← evita el error de null
        $detallePedido->save();

        return redirect('/detalle')->with('success', 'Detalle de pedido creado con éxito.');
    }

    public function show(Request $request)
    {
        $id = $request->input('id_detalle');
        $detallePedido = DetallePedido::find($id);
        if (!$detallePedido) {
            return redirect()->back()->with('error', 'El detalle no se encontró.');
        }
        return view('/detalle/showDetalle', compact('detallePedido'));
    }

    public function edit(DetallePedido $detallePedido)
    {
        $pedidosUsuario = Pedido::where('id_user', auth()->id())->get();
        return view('detalle.editDetalle', compact('detallePedido', 'pedidosUsuario'));
    }

    public function update(Request $request, $id)
    {
        $detallePedido = DetallePedido::find($id);

        if (!$detallePedido) {
            return redirect()->route('detalle.index')->with('error', 'El detalle no se encontró.');
        }

        if ($request->has('total')) {
            $detallePedido->total_carro = $request->input('total');
        }

        $detallePedido->save();

        return redirect()->route('detalle.index')->with('success', 'El detalle se ha actualizado con éxito.');
    }

    public function destroy(DetallePedido $detallePedido)
    {
        $detallePedido->delete();
        return redirect()->route('detalle.index')->with('success', 'El detalle se ha eliminado con éxito.');
    }
}
