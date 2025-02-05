<?php

namespace App\Http\Controllers;

use App\Models\Abono;
use App\Models\Cliente;
use Illuminate\Http\Request;

class AbonoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $abono = new Abono ();

        $abonoIndex = Abono::all();
        return view('abono/abonoIndex', compact ('abonoIndex'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('abono/createAbono');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'monto_abono' => 'required|numeric|max:999999.99|min:0',
            'id_cliente' => 'required|integer|exists:clientes,id_cliente',
        ], [
            'monto_abono.required' => 'El campo monto es obligatorio.',
            'monto_abono.numeric' => 'El campo precio debe ser un número.',
            'monto_abono.max'=>'El campo precio no puede ser mayor a 999,999.99',
            'monto_abono.min' => 'El campo precio no puede ser negativo.',
            'id_cliente.required' => 'Debes seleccionar un cliente para la compra.',
            'id_cliente.integer' => 'El ID del cliente debe ser un número entero.',
            'id_cliente.exists' => 'El cliente seleccionado no está registrado.',
        ]);
        $abono = new Aceite();
        $abono->monto_abono = $request->monto_abono;
        
        if ($abono->save()) {
            return redirect('/abono')->with('success', 'Abono registrado correctamente.');
        } else {
            return redirect()->back()->withErrors(['Error al guardar el abono. Por favor, intenta de nuevo.']);
        } 
    }

    /**
     * Display the specified resource.
     */
    public function show(Abono $abono)
    {
        $id = $request->input('id_abono');
        $abono = Abono::find($id);
            
        if (!$abono) {
            return redirect()->back()->with('error', 'El abono no se encontró.');
        }
        return view('/abono/showAbono', ['abono' => $abono]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Abono $abono)
    {
        // $user = Auth::user();
        // if($user->isAdmin())
        // {
            $abono = Abono::find($id);

            if (!$abono) {
                return redirect()->route('abono.abonoIndex')->with('error', 'El abono no se encontró.');
            // }

            return view('/abono/editAbono', ['abono' => $abono]);   
        }
        // else{
        //     return redirect()->back()->with('error', 'No puedes editar este abono.');
        // }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Abono $abono)
    {
        $request->validate([
            'monto_abono' => 'required|numeric|max:999999.99|min:0'
        ], [
            'monto_abono.required' => 'El campo monto es obligatorio.',
            'monto_abono.numeric' => 'El campo precio debe ser un número.',
            'monto_abono.max'=>'El campo precio no puede ser mayor a 999,999.99',
            'monto_abono.min' => 'El campo precio no puede ser negativo.',

        ]);
        $abono = Abono::find($id);
    
        if (!$abono) {
            return redirect()->route('abono.abonoIndex')->with('error', 'El abono no se encontró.');
        }
        $abono->monto_abono = $request->monto_abono;
        //dd($request);
        // if ($request->hasFile('archivo') && $request->file('archivo')->isValid()) {
        //     // Eliminar el archivo antiguo si existe
        //     if ($aceite->archivo_ubicacion) {
        //         Storage::delete($aceite->archivo_ubicacion); 
        //     }

        //     $aceite->archivo_nombre = $request->file('archivo')->getClientOriginalName();
        //     $aceite->archivo_ubicacion = $request->file('archivo')->store('public/img');
        //     //dd($aceite);
        // }
            /*Eliminar las imagenes antiguas
                Storage::delete($aceite->aceite_ubicacion);
                $aceite->aceite_ubicacion->delete();
            
                $aceite->archivo_nombre = $request->file('archivo')->getClientOriginalName();
                $aceite->archivo_ubicacion = $request->file('archivo')->store('public/img');*/
        $abono->save();
        return redirect()->route('abono.abonoIndex')->with('success', 'El abono se ha actualizado con éxito.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Abono $abono)
    {
        $abono = Abono::find($id);

        // if ($aceite->archivo_ubicacion) {
        //     Storage::delete($aceite->archivo_ubicacion);
        // }

        if (!$abono) {
            return redirect()->route('abono.abonoIndex')->with('error', 'El abono no se encontró.');
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

        $abono->delete();

        return redirect()->route('abono.abonoIndex')->with('success', 'El abono se ha eliminado con éxito.');
    }
}
