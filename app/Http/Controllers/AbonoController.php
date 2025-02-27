<?php

namespace App\Http\Controllers;

use App\Models\Abono;
use App\Models\Cliente;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;

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
            'monto_abono' => 'required|numeric|min:0',
            'nombre_usuario' => 'required|string|unique:clientes,nombre_usuario',
        ], [
            'monto_abono.required' => 'El monto del abono es obligatorio.',
            'monto_abono.numeric' => 'El monto del abono debe ser un número.',
            'monto_abono.min' => 'El monto del abono no puede ser negativo.',
            'nombre_usuario.required' => 'Debe seleccionar el cliente que da el abono.',
            'nombre_usuario.string' => 'El nombre de usuario debe ser una cadena de texto',
            'nombre_usuario.unique' => 'El nombre del usuario seleccionado debe ser único.',
        ]);
        $abono = new Aceite();
        $abono->monto_abono = $request->monto_abono;
        $abono->nombre_usuario = $nombre_usuario;
        
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
            $cliente = Cliente::all();

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
            'monto_abono' => 'required|numeric|min:0',
            'nombre_usuario' => 'required|string|unique:clientes,nombre_usuario',
        ], [
            'monto_abono.required' => 'El monto del abono es obligatorio.',
            'monto_abono.numeric' => 'El monto del abono debe ser un número.',
            'monto_abono.min' => 'El monto del abono no puede ser negativo.',
            'nombre_usuario.required' => 'Debe seleccionar el cliente que da el abono.',
            'nombre_usuario.string' => 'El nombre de usuario debe ser una cadena de texto',
            'nombre_usuario.unique' => 'El nombre del usuario seleccionado debe ser único.',
        ]);
        $abono = Abono::find($id);
    
        if (!$abono) {
            return redirect()->route('abono.abonoIndex')->with('error', 'El abono no se encontró.');
        }
        $abono->monto_abono = $request->monto_abono;
        $abono->nombre_usuario = $nombre_usuario;
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
