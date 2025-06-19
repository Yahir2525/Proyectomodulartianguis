<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Carro;
use App\Models\Abono;
use App\Models\Cliente;
use App\Models\Compra;
use App\Models\Credito;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\DetallePedido;
use App\Models\Vendedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CarroController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $carroIndex = Carro::with('productos')->where('id_user', $userId)->get();
        return view('carro/carroIndex', compact('carroIndex'));
    }

    public function create()
    {
        $usuarioId = Auth::id();
        $pedidosUsuario = Pedido::where('id_user', $usuarioId)->get();

        $reservas = Carro::selectRaw('id_producto, SUM(cantidad) as reservadas')
            ->groupBy('id_producto')
            ->pluck('reservadas', 'id_producto');

        $productos = Producto::all();

        foreach ($productos as $producto) {
            $producto->piezas_disponibles = $producto->piezas - ($reservas[$producto->id_producto] ?? 0);
        }

        return view('carro/createCarro', compact('usuarioId', 'pedidosUsuario', 'productos'));
    }

    public function store(Request $request)
    {
        $userId = $request->input('id_user');

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

        $producto = Producto::find($request->input('id_producto'));
        if (!$producto) {
            return redirect()->back()->with('error', 'Producto no encontrado.');
        }

        $cantidadSolicitada = $request->input('cantidad');
        if ($cantidadSolicitada <= 0) {
            return redirect()->back()->with('error', 'La cantidad debe ser mayor a 0.');
        }

        $existeProducto = Carro::where('id_user', $userId)
            ->where('id_pedido', $pedidoId)
            ->where('id_producto', $producto->id_producto)
            ->first();

        if ($existeProducto) {
            return redirect()->back()->with('error', 'Este producto ya está en el carrito.');
        }

        $carrosReservados = Carro::where('id_producto', $producto->id_producto)->sum('cantidad');
        $piezasDisponibles = max(0, $producto->piezas - $carrosReservados);

        if ($cantidadSolicitada > $piezasDisponibles) {
            return redirect()->back()->with('error', 'No hay suficientes piezas disponibles. Solo quedan ' . $piezasDisponibles . ' disponibles.');
        }

        $carro = new Carro();
        $carro->id_user = $userId;
        $carro->id_pedido = $pedidoId;
        $carro->id_producto = $producto->id_producto;
        $carro->cantidad = $cantidadSolicitada;
        $carro->save();

        return redirect('/carro')->with('success', 'Producto agregado al carrito.');
    }

    public function show(Request $request)
    {
        $id = $request->input('id_carro');
        $carro = Carro::find($id);
        if (!$carro) {
            return redirect()->back()->with('error', 'El carro no se encontró.');
        }

        $reservas = Carro::selectRaw('id_producto, SUM(cantidad) as reservadas')
            ->groupBy('id_producto')
            ->pluck('reservadas', 'id_producto');

        $productos = Producto::all();

        foreach ($productos as $producto) {
            $producto->piezas_disponibles = $producto->piezas - ($reservas[$producto->id_producto] ?? 0);
        }

        
        return view('/carro/showCarro', compact('carro', 'productos'));
    }

    public function edit(Carro $carro)
    {
        $reservas = Carro::selectRaw('id_producto, SUM(cantidad) as reservadas')
            ->groupBy('id_producto')
            ->pluck('reservadas', 'id_producto');

        $productos = Producto::all();

        foreach ($productos as $producto) {
            $producto->piezas_disponibles = $producto->piezas - ($reservas[$producto->id_producto] ?? 0);
        }

        $pedidosUsuario = Pedido::where('id_user', auth()->id())->get();
        return view('carro.editCarro', compact('carro', 'productos', 'pedidosUsuario'));
    }

    public function update(Request $request, Carro $carro)
    {
        $producto = Producto::find($request->input('id_producto'));
        if (!$producto) {
            return redirect()->back()->with('error', 'Producto no encontrado.');
        }

        $cantidadSolicitada = $request->input('cantidad');
        if ($cantidadSolicitada <= 0) {
            return redirect()->back()->with('error', 'La cantidad debe ser mayor a 0.');
        }

        $carrosReservados = Carro::where('id_producto', $producto->id_producto)
            ->where('id_carro', '!=', $carro->id_carro)
            ->sum('cantidad');

        $piezasDisponibles = max(0, $producto->piezas - $carrosReservados);

        if ($cantidadSolicitada > $piezasDisponibles) {
            return redirect()->back()->with('error', 'No hay suficientes piezas disponibles. Solo quedan ' . $piezasDisponibles . ' disponibles.');
        }

        $carro->id_producto = $producto->id_producto;
        $carro->id_pedido = $request->input('id_pedido');
        $carro->cantidad = $cantidadSolicitada;
        $carro->save();

        return redirect()->route('carro.index')->with('success', 'El carro se ha actualizado con éxito.');
    }

    public function destroy(Carro $carro)
    {
        $carro->delete();
        return redirect()->route('carro.index')->with('success', 'El carro se ha eliminado con éxito.');
    }
}
