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

        DB::beginTransaction();

        try {
            $abono->save();

            $credito = Credito::find($abono->id_credito);

            if ($credito) {
                $credito->saldo_total -= $abono->monto_abono;
                if ($credito->saldo_total < 0) {
                    $credito->saldo_total = 0; // evitar saldo negativo
                }
                $credito->save();
            }

            DB::commit();

            return redirect('/abono')->with('success', 'Abono registrado y saldo actualizado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['Error al registrar el abono: ' . $e->getMessage()]);
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

    public function update(Request $request, $id)
    {
        $abono = Abono::find($id);

        if (!$abono) {
            return redirect()->route('abono.abonoIndex')->with('error', 'El abono no se encontró.');
        }

        $montoAnterior = $abono->monto_abono;
        $nuevoMonto = $request->input('monto_abono');

        $abono->monto_abono = $nuevoMonto;
        $abono->save();

        $credito = Credito::find($abono->id_credito);
        if ($credito) {
            $diferencia = $montoAnterior - $nuevoMonto;
            $credito->saldo_total += $diferencia; // Si nuevo abono es menor, el saldo sube

            if ($credito->saldo_total < 0) $credito->saldo_total = 0;
            $credito->save();
        }

        return redirect()->route('abono.abonoIndex')->with('success', 'El abono se ha actualizado con éxito.');
    }


    public function destroy(Abono $abono)
    {
        $abono = Abono::find($abono->id_abono);

        if (!$abono) {
            return redirect()->route('abono.index')->with('error', 'El abono no se encontró.');
        }

        $credito = Credito::find($abono->id_credito);
        if ($credito) {
            $credito->saldo_total += $abono->monto_abono;
            $credito->save();
        }

        $abono->delete();

        return redirect()->route('abono.index')->with('success', 'El abono se ha eliminado con éxito.');
    }

    public function aplicarAlCredito($id)
    {
        $abono = Abono::find($id);

        if (!$abono) {
            return redirect()->back()->with('error', 'Abono no encontrado.');
        }

        $credito = Credito::find($abono->id_credito);

        if (!$credito) {
            return redirect()->back()->with('error', 'Crédito no encontrado.');
        }

        // Restar el abono al saldo del crédito
        $credito->saldo_total -= $abono->monto_abono;

        if ($credito->saldo_total < 0) {
            $credito->saldo_total = 0;
        }

        // Si el crédito ya está pagado
        if ($credito->saldo_total == 0) {
            $credito->estado = 0; // Opcional: marcar como pagado
            $credito->fecha_liquidacion = now();
        }

        $credito->save();

        return redirect()->back()->with('success', 'Monto del abono aplicado al crédito correctamente.');
    }


}
