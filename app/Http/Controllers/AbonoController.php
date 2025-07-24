<?php

namespace App\Http\Controllers;
use App\Models\Abono;
use App\Models\Carro;
use App\Models\CarroProducto;
use App\Models\Credito;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class AbonoController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('administrador')) {
            $abonoIndex = Abono::all();
        } else {
            $abonoIndex = Abono::where('id_user', $user->id)->get();
        }

        return view('abono/abonoIndex', compact('abonoIndex'));
    }

    public function create()
    {
        return view('abono/createAbono');
    }

    public function store(Request $request)
    {
        $abono = new Abono();
        $abono->id_credito = $request->input('id_credito');
        $abono->id_user = $request->input('id_user');
        $abono->monto_abono = $request->monto_abono;
        
        if ($abono->save()) {
            return redirect('/abono')->with('success', 'Abono registrado correctamente.');
        } else {
            return redirect()->back()->withErrors(['Error al guardar el abono. Por favor, intenta de nuevo.']);
        } 
    }

    public function show(Request $request)
    {
        $id = $request->input('id_abono');
        $abono = Abono::find($id);
            
        if (!$abono) {
            return redirect()->back()->with('error', 'El abono no se encontró.');
        }
        return view('/abono/showAbono', ['abono' => $abono]);
    }

    public function edit($id)
    {
        // $user = Auth::user();
        // if($user->isAdmin())
        // {
            $abono = Abono::find($id);
            // $cliente = Cliente::all();

            if (!$abono) {
                return redirect()->back()->with('error', 'El abono no se encontró.');
                    // return redirect()->route('/producto/productoIndex')->with('error', 'El producto no se encontró.');
            }
            return view('/abono/editAbono', ['abono' => $abono]);
        // else{
        //     return redirect()->back()->with('error', 'No puedes editar este abono.');
        // }
    }

    public function update(Request $request, Abono $abono)
    {
        $abono = Abono::find($id);
    
        if (!$abono) {
            return redirect()->route('abono.abonoIndex')->with('error', 'El abono no se encontró.');
        }
        $abono->id_credito = $idCredito;
        $abono->monto_abono = $request->monto_abono;
        $abono->nombre_usuario = $nombre_usuario;
        $abono->save();
        return redirect()->route('abono.abonoIndex')->with('success', 'El abono se ha actualizado con éxito.');
    }

    public function destroy(Abono $abono)
    {
        $abono = Abono::find($abono->id_abono);

        if (!$abono) {
            return redirect()->route('abono.index')->with('error', 'El abono no se encontró.');
        } 

        $abono->delete();

        return redirect()->route('abono.index')->with('success', 'El abono se ha eliminado con éxito.');
    }
}
