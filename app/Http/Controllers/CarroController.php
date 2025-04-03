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

class CarroController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $carro = new Carro ();

        Carro::all();
        $carroIndex = Carro::with('productos')->get();
        // dd($carroIndex);
        return view('carro/carroIndex', compact ('carroIndex'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('carro/createCarro');
    }

    public function store(Request $request)
    {
        $micarro = Carro::where('id_producto', $request->input('id_producto')) ;
        if($micarro){
            return redirect()->back()->with('error', 'El producto ya está.');        
        }

        $carro = new Carro();
        $carro->id_producto = $request->input('id_producto');
        $carro->cantidad = 1;
        $carro->estado_producto = 1;


        if ($carro->save()) {
            return redirect('/carro')->with('success', 'Carro registrado correctamente.');
        }
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

        $carro->cantidad = $request->input('cantidad');
        $carro->estado_producto = $request->input('estado_producto');
        

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
