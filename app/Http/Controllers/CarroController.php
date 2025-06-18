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
use App\Models\Vendedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class CarroController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $carro = new Carro ();

        Carro::all();
        $carroIndex = Carro::with('productos')->where('id_user', $userId)->get();

        return view('carro/carroIndex', compact ('carroIndex'));
    }

    public function create()
    {
        $usuarioId = Auth::id();
        $pedidosUsuario = Pedido::where('id_user', $usuarioId)->get();
        $productos = Producto::all();
        return view('carro/createCarro', compact('usuarioId', 'pedidosUsuario','productos'));
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

        $producto = Producto::find($request->input('id_producto'));
        if (!$producto) {
            return redirect()->back()->with('error', 'Producto no encontrado.');
        }

        $existeProducto = Carro::where('id_user', $userId)->where('id_pedido', $pedidoId)->where('id_producto', $producto->id_producto)->first();

        if ($existeProducto) {
            return redirect()->back()->with('error', 'Este producto ya está en el carrito.');
        }

        $cantidad = $request->input('cantidad');
        if ($producto->piezas < $cantidad) {
            return redirect()->back()->with('error', 'No hay suficientes piezas disponibles.');
        }

        $carro = new Carro();
        $carro->id_user = $request->input('id_user');
        $carro->id_pedido = $pedidoId;
        $carro->id_producto = $producto->id_producto;
        $carro->cantidad = $cantidad;
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
        return view('/carro/showCarro', ['carro' => $carro]);
    }

    public function edit(Carro $carro)
    {
        $carro = Carro::find($carro->id_carro);
        $productos = Producto::all();
        $pedidosUsuario = Pedido::where('id_user', auth()->id())->get(); // o como obtengas los pedidos del usuario
        return view('carro.editCarro', compact('carro', 'productos', 'pedidosUsuario'));
    }

    public function update(Request $request, Carro $carro)
    {
        $carro = Carro::find($carro->id_carro);
        $carro->id_producto = $request->input('id_producto');
        $carro->id_pedido = $request->input('id_pedido'); 
        $carro->cantidad = $request->input('cantidad');

        if (!$carro) {
            return redirect()->route('carro.index')->with('error', 'El carro no se encontró.');
        }

        $carro->save();
        return redirect()->route('carro.index')->with('success', 'El carro se ha actualizado con éxito.');
    }

    public function destroy(Carro $carro)
    {
        $carro = Carro::find($carro->id_carro);
        
        if (!$carro) {
            return redirect()->route('carro.index')->with('error', 'El carro no se encontró.');
        }
        $carro->delete();

        return redirect()->route('carro.index')->with('success', 'El carro se ha eliminado con éxito.');
    }
}
