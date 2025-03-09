<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Compra;
use App\Models\Producto;
use Illuminate\Http\Request;

class PedidoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pedido = new Pedido ();

        $pedidoIndex = Pedido::all();
        return view('pedido/pedidoIndex', compact ('pedidoIndex'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pedido/createPedido');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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

        // $aceitesSeleccionados = $request->aceites;
        // $aceitesUnicos = array_unique($aceitesSeleccionados);

        // if (count($aceitesSeleccionados) !== count($aceitesUnicos)) {
        //     return redirect()->back()->with('error', 'No puedes seleccionar el mismo aceite más de una vez en la misma compra.');
        // }

        $pedido = new Pedido();
        $pedido->id_compra = $idCompra;
        $pedido->id_producto = $idProducto;
        $pedido->cantidad = $request->cantidad;
        // $quantities = $request->input('cantidad');
        $pedido->precio_unitario = $precio_unitario;
        $pedido->subtotal = $request->subtotal;
        $pedido->total_pagar = $request->total_pagar;
        
        if ($pedido->save()) {
            return redirect('/pedido')->with('success', 'Pedido registrado correctamente.');
        } else {
            return redirect()->back()->withErrors(['Error al guardar el pedido. Por favor, intenta de nuevo.']);
        } 
    
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        $id = $request->input('id_pedido');
        $pedido = Pedido::find($id);
        // dd($id);
        if (!$pedido) {
            return redirect()->back()->with('error', 'El pedido no se encontró.');
        }
        return view('/pedido/showPedido', ['pedido' => $pedido]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $pedido = Pedido::find($id);
        // $compra = Compra::all();
        // $producto = Producto::all();
        // dd($pedido);
        if (!$pedido) {
            return redirect()->back()->with('error', 'El pedido no se encontró.');
                // return redirect()->route('/pedido/pedidoIndex')->with('error', 'El pedido no se encontró.');
        }
        return view('/pedido/editPedido', ['pedido' => $pedido]);
    }


    /**
     * Update the specified resource in storage.
     */
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
        $pedido->cantidad = $request->cantidad;
        // $quantities = $request->input('cantidad');
        $pedido->precio_unitario = $precio_unitario;
        $pedido->subtotal = $request->subtotal;
        $pedido->total_pagar = $request->total_pagar;
        $pedido->save();
        return redirect()->route('pedido.pedidoIndex')->with('success', 'El pedido se ha actualizado con éxito.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $pedido = Pedido::find($id);

        // if ($aceite->archivo_ubicacion) {
        //     Storage::delete($aceite->archivo_ubicacion);
        // }

        if (!$pedido) {
            return redirect()->route('pedido.index')->with('error', 'El pedido no se encontró.');
        }
        
        // $detalleCompras = DetalleCompra::where('id_aceite', $id)->get();

        // foreach ($detalleCompras as $detalleCompra) {
        //     $compra = Compras::find($detalleCompra->id_compras);
        //     if ($compra) {
        //         $compra->delete();
        //     }
            
        //     // Eliminar el DetalleCompras
        //     $detalleCompra->delete();
        // }

        $pedido->delete();

        return redirect()->route('pedido.index')->with('success', 'El pedido se ha eliminado con éxito.');
    }
}
