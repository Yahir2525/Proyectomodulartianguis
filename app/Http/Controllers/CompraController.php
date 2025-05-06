<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Abono;
use App\Models\Cliente;
use App\Models\Compra;
use App\Models\Credito;
use App\Models\Carro;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Vendedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompraController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $compra = new Compra ();

        $compraIndex = Compra::all();
        return view('compra/compraIndex', compact ('compraIndex'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('compra/createCompra');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // $request->validate([
        //     'id_pedido' => 'required|integer|unique:pedidos,id_pedido',
        //     'nombre_usuario' => 'required|string|unique:clientes,nombre_usuario',
        //     'total_pagar' => 'required|numeric|min:0',
        //     'estado_compra' => 'required|boolean',
        // ], [
        //     'id_pedido.required' => 'Debe seleccionar un pedido.',
        //     'id_pedido.integer' => 'El ID del pedido debe ser un número entero.',
        //     'id_pedido.unique' => 'El ID del pedido debe ser único.',
        //     'nombre_usuario.required' => 'Debe seleccionar un cliente para la compra.',
        //     'nombre_usuario.string' => 'El nombre de usuario debe ser una cadena de texto.',
        //     'nombre_usuario.unique' => 'El nombre del usuario seleccionado debe ser único.',
        //     'estado_compra.required' => 'Debe seleccionar un estado de la compra.',
        //     'estado_compra.boolean' => 'El estado debe ser activo o desactivo.', 
        // ]);

        // $compra = Compra::create([
        //     'id_usuario' => auth()->id(),
        //     'estado_compra' => 1,
        // ]);
    
        // // 2. Obtener los productos del carrito
        // $carros = Carro::with('producto')->where('nombre_usuario', auth()->nombre_usuario())->get();
    
        // // 3. Crear order_products desde el carrito
        // foreach ($carros as $carro) {
        //     $pedido = new Perido();
        //         $pedido->id_compra = $request->input('id_compra');
        //         $pedido->id_producto = $request->input('id_producto');
        //         $pedido->cantidad = $request->input('cantidad');
        //         $pedido->precio_unitario = $request->precio_unitario;
        //         $pedido->subtotal += $pedido->precio_unitario * $pedido->cantidad;
        //         $pedido->total_pagar += $pedido->subtotal;
        // }
    
        // 4. Vaciar el carrito
        // Carro::where('nombre_usuario', auth()->id())->delete();
    
        $compra = new Compra();
        $compra->id_compra = $request->input('id_compra');
        $compra->nombre_usuario = $request->input('nombre_usuario');
        $compra->estado_compra = $request->estado_compra;

        $carros = Carro::whereNull('id_compra')->get();
    
        foreach ($carros as $carro) {
            $carro->update([
                $compra->id_compra = $request->input('id_compra') // Actualiza el carrito con el id_compra
            ]);
            // dd($compra);
        }

        
        if ($compra->save()) {
            return redirect('/compra')->with('success', 'Compra registrado correctamente.');
        } else {
            return redirect()->back()->withErrors(['Error al guardar la compra. Por favor, intenta de nuevo.']);
        } 
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        $id = $request->input('id_compra');
        $compra = Compra::find($compra);
            
        if (!$compra) {
            return redirect()->back()->with('error', 'La compra no se encontró.');
        }
        return view('/compra/showCompra', ['compra' => $compra]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $compra = Compra::find($id);
        // $pedido = Pedido::all();
        // $cliente = Cliente::all();

        if (!$compra) {
            return redirect()->back()->with('error', 'La compra no se encontró.');
                // return redirect()->route('/producto/productoIndex')->with('error', 'El producto no se encontró.');
        }
        return view('/compra/editCompra', ['compra' => $compra]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Compra $compra)
    {
        $request->validate([
            'id_pedido' => 'required|integer|unique:pedidos,id_pedido',
            'nombre_usuario' => 'required|string|unique:clientes,nombre_usuario',
            'estado_compra' => 'required|boolean',
        ], [
            'id_pedido.required' => 'Debe seleccionar un pedido.',
            'id_pedido.integer' => 'El ID del pedido debe ser un número entero.',
            'id_pedido.unique' => 'El ID del pedido debe ser único.',
            'nombre_usuario.required' => 'Debe seleccionar un cliente para la compra.',
            'nombre_usuario.string' => 'El nombre de usuario debe ser una cadena de texto.',
            'nombre_usuario.unique' => 'El nombre del usuario seleccionado debe ser único.',
            'estado_compra.required' => 'Debe seleccionar un estado de la compra.',
            'estado_compra.boolean' => 'El estado debe ser activo o desactivo.',            
        ]);
        $compra = Compra::find($id);
    
        if (!$compra) {
            return redirect()->route('compra.compraIndex')->with('error', 'La compra no se encontró.');
        }
        $compra->estado_compra = $request->estado_compra;
        $compra->save();
        return redirect()->route('compra.compraIndex')->with('success', 'La compra se ha actualizado con éxito.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Compra $compra)
    {    
        $compra = Compra::find($compra->id_compra);

        // if ($aceite->archivo_ubicacion) {
        //     Storage::delete($aceite->archivo_ubicacion);
        // }

        if (!$compra) {
            return redirect()->route('compra.index')->with('error', 'La compra no se encontró.');
        }


        $compra->delete();

        // $pedido = Pedido::where('id_pedido', $id)->get();
        // $pedido->delete();

        return redirect()->route('compra.index')->with('success', 'La compra se ha eliminado con éxito.');
    }
}
