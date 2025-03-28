<?php

namespace App\Http\Controllers;

use App\Models\Abono;
use App\Models\Cliente;
use App\Models\Compra;
use App\Models\Credito;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Vendedor;
use Illuminate\Http\Request;

class PedidoController extends Controller
{
    public function index()
    {
        $pedido = new Pedido ();

        $pedidoIndex = Pedido::all();
        return view('pedido/pedidoIndex', compact ('pedidoIndex'));
    }

    public function create()
    {
        return view('pedido/createPedido');
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_compra' => 'required|integer|unique:compras,id_compra',
            'id_producto' => 'required|integer|unique:productos,id_producto',
            'cantidad' => 'required|integer|min:0',
            'precio_unitario' => 'required|numeric',
            'subtotal' => 'required|numeric|min:0',
            'total_pagar' => 'required|numeric|min:0',

        ], [
            'compra_id.required' => 'Debe seleccionar una compra .',
            'compra_id.integer' => 'El ID de la compra debe ser un número entero.',
            'compra_id.unique' => 'El ID de la compra debe ser único.',
            'producto_id.required' => 'Debe seleccionar un producto .',
            'producto_id.integer' => 'El ID del producto debe ser un número entero.',
            'producto_id.unique' => 'El ID del producto debe ser único.',
            'cantidad.required' => 'La cantidad de productos es obligatoria.',
            'cantidad.integer' => 'La cantidad debe ser un número entero',
            'cantidad.min' => 'La cantidad no puede ser negativa.',
            'precio_unitario.required' => 'El precio unitario es obligatorio.',
            'precio_unitario.numeric' => 'El precio unitario debe ser un número.',
            'subtotal.required' => 'El subtotal es obligatorio.',
            'subtotal.numeric' => 'El subtotal debe ser un número.',
            'subtotal.min' => 'El subtotal no puede ser negativo.',
            'total_pagar.required' => 'El total a pagar es obligatorio.',
            'total_pagar.numeric' => 'El total a pagar debe ser un número.',
            'total_pagar.min' => 'El total a pagar no puede ser negativo.',

        ]);
        $pedido = new Pedido();
        $pedido->id_compra = $id_compra;
        $pedido->id_producto = $id_producto;
        // $pedido->cantidad = $request->cantidad;
        $pedido->cantidad = $request->input('cantidad');

        // if ($request->input('action') == 'increase') {
        //     $cantidad++;
        // } elseif ($request->input('action') == 'decrease' && $cantidad > 1) {
        //     $cantidad--;
        // }
    
        // // Actualiza la cantidad
        // $pedido->cantidad = $cantidad;

        $pedido->precio_unitario = $request->precio_unitario;
        $pedido->subtotal += $pedido->precio_unitario * $pedido->cantidad;
        $pedido->total_pagar += each($pedido->subtotal);

        $compra = Compra::find($id_compra);  // Buscamos la compra

        // Calculamos el nuevo total a pagar sumando todos los subtotales de los pedidos de la compra
        $totalPagar = $compra->pedido->sum('subtotal');
        
        // Actualizamos el campo 'total_pagar' de la compra
        $compra->total_pagar = $totalPagar;
        
        
        if ($pedido->save()) {
            return redirect('/pedido')->with('success', 'Pedido registrado correctamente.');
        } else {
            return redirect()->back()->withErrors(['Error al guardar el pedido. Por favor, intenta de nuevo.']);
        } 

        $compra = Compra::find($idCompra);

        if($compra){
            $credito = Credito::find($compra->id_credito);
            if ($credito) {
                $credito->saldo_total -= $pedido->total_pagar;
        }
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
        // $compra = Compra::all();
        // $producto = Producto::all();
        if (!$pedido) {
            return redirect()->back()->with('error', 'El pedido no se encontró.');
        }
        return view('/pedido/editPedido', ['pedido' => $pedido]);
    }

    public function update(Request $request, Pedido $pedido)
    {
        $request->validate([
            'id_compra' => 'required|integer|unique:compras,id_compra',
            'id_producto' => 'required|integer|unique:productos,id_producto',
            'cantidad' => 'required|integer|min:0',
            'precio_unitario' => 'required|numeric|exists:productos,nombre_usuario',
            'subtotal' => 'required|numeric|min:0',
            'total_pagar' => 'required|numeric|min:0',

        ], [
            'compra_id.required' => 'Debe seleccionar una compra .',
            'compra_id.integer' => 'El ID de la compra debe ser un número entero.',
            'compra_id.unique' => 'El ID de la compra debe ser único.',
            'producto_id.required' => 'Debe seleccionar un producto .',
            'producto_id.integer' => 'El ID del producto debe ser un número entero.',
            'producto_id.unique' => 'El ID del producto debe ser único.',
            'cantidad.required' => 'La cantidad de productos es obligatoria.',
            'cantidad.integer' => 'La cantidad debe ser un número entero',
            'cantidad.min' => 'La cantidad no puede ser negativa.',
            'precio_unitario.required' => 'El precio unitario es obligatorio.',
            'precio_unitario.numeric' => 'El precio unitario debe ser un número.',
            'precio_unitario.exists' => 'El precio unitario debe existir',
            'subtotal.required' => 'El subtotal es obligatorio.',
            'subtotal.numeric' => 'El subtotal debe ser un número.',
            'subtotal.min' => 'El subtotal no puede ser negativo.',
            'total_pagar.required' => 'El total a pagar es obligatorio.',
            'total_pagar.numeric' => 'El total a pagar debe ser un número.',
            'total_pagar.min' => 'El total a pagar no puede ser negativo.',

        ]);
        $pedido = Pedido::find($id);
        
    
        if (!$pedido) {
            return redirect()->route('pedido/pedidoIndex')->with('error', 'El pedido no se encontró.');
        }
        $pedido->id_compra = $idCompra;
        $pedido->id_producto = $idProducto;
        $pedido->cantidad = $request->input('cantidad');

        // Ajusta la cantidad según la acción (aumentar o disminuir)
        $pedido->precio_unitario = $precio_unitario;
        $pedido->subtotal += $pedido->precio_unitario * $pedido->cantidad;
        $pedido->total_pagar = $request->total_pagar;
        $pedido->save();
        return redirect()->route('pedido.pedidoIndex')->with('success', 'El pedido se ha actualizado con éxito.');
    }

    public function destroy($id)
    {
        $pedido = Pedido::find($id);
        if (!$pedido) {
            return redirect()->route('pedido.index')->with('error', 'El pedido no se encontró.');
        }
        $pedido->delete();

        return redirect()->route('pedido.index')->with('success', 'El pedido se ha eliminado con éxito.');
    }
}
