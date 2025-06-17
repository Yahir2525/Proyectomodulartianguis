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
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $userId = Auth::id();

        $carro = new Carro ();

        Carro::all();
        $carroIndex = Carro::with('productos')->where('id_user', $userId)->get();

        return view('carro/carroIndex', compact ('carroIndex'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $usuarioId = Auth::id();
        $pedidosUsuario = Pedido::where('id_user', $usuarioId)->get();
        // Obtener los pedidos activos del usuario logueado
        $productos = Producto::all(); // Traer todos los productos
        return view('carro/createCarro', compact('usuarioId', 'pedidosUsuario','productos'));
    }

    public function store(Request $request)
    {
        $userId = $request->input('id_user');
        
        // Si se marcó "crear nuevo pedido", se ignora el input de id_pedido y se crea uno nuevo
        if ($request->has('nuevo_pedido')) {
            // Evitar duplicados: aquí simplemente usamos auto-increment, pero podrías validar otros criterios si lo deseas
            $pedido = new Pedido();
            $pedido->id_user = $request->input('id_user');
            $pedido->id_credito = $request->input('id_credito');
            $pedido->estado_pedido = 1;
            $pedido->save();
            $pedidoId = $pedido->id_pedido;
        } else {
            $pedidoId = $request->input('id_pedido');

            // Validación: si no se seleccionó nada y tampoco se marcó crear nuevo
            if (!$pedidoId) {
                return redirect()->back()->with('error', 'Debes seleccionar un pedido o crear uno nuevo.');
            }
        }

        // Verificar existencia del producto
        $producto = Producto::find($request->input('id_producto'));
        if (!$producto) {
            return redirect()->back()->with('error', 'Producto no encontrado.');
        }

        // Validar que no esté repetido en el mismo carrito
        $existeProducto = Carro::where('id_user', $userId)->where('id_pedido', $pedidoId)->where('id_producto', $producto->id_producto)->first();

        if ($existeProducto) {
            return redirect()->back()->with('error', 'Este producto ya está en el carrito.');
        }

        // Validar stock disponible
        $cantidad = $request->input('cantidad');
        if ($producto->piezas < $cantidad) {
            return redirect()->back()->with('error', 'No hay suficientes piezas disponibles.');
        }

        // Agregar al carro
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
        return view('/carro/editCarro', ['carro' => $carro]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Carro $carro)
    {
        $carro = Carro::find($carro->id_carro);
        $carro->id_producto = $request->input('id_producto');
        $carro->cantidad = $request->input('cantidad');

        if (!$carro) {
            return redirect()->route('carro.index')->with('error', 'El carro no se encontró.');
        }

        $carro->save();
        return redirect()->route('carro.index')->with('success', 'El carro se ha actualizado con éxito.');
    }

    /**
     * Remove the specified resource from storage.
     */
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
