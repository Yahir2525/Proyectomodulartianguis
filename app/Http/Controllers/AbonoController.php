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
        $user = Auth::user();

        if ($user->hasRole('administrador')) {
            $usuarios = User::all();
            $creditos = Credito::with('user')->get();
        } else {
            $usuarios = collect(); // vacío
            $creditos = Credito::with('user')->where('id_user', $user->id_user)->get();
        }

        return view('abono.createAbono', compact('usuarios', 'creditos'));
    }



    public function store(Request $request)
    {
        $abono = new Abono();
        $abono->id_credito = $request->input('id_credito');
        $abono->id_user = $request->input('id_user');
        $abono->monto_abono = $request->input('monto_abono');
        $abono->save();

        $credito = Credito::find($abono->id_credito);
        if ($credito) {
            $credito->saldo_total -= $abono->monto_abono;
            if ($credito->saldo_total < 0) {
                $credito->saldo_total = 0;
            }
            $credito->save();
        }

        return redirect('/abono')->with('success', 'Abono registrado y saldo actualizado correctamente.');
    }



    public function show(Request $request)
    {
        $idAbono = $request->input('id_abono');
        $nombreUsuario = $request->input('nombre_usuario');

        // Buscar por ID de abono
        if ($idAbono) {
            $abono = Abono::with('user', 'credito')->find($idAbono);
            if (!$abono) {
                return back()->with('error', 'El abono no se encontró.');
            }
            return view('abono.showAbono', ['abonos' => collect([$abono])]);
        }

        // Buscar por nombre de usuario
        if ($nombreUsuario) {
            $usuario = User::where('nombre_usuario', 'ILIKE', $nombreUsuario)->first();
            if (!$usuario) {
                return back()->with('error', 'Usuario no encontrado.');
            }

            $abonos = Abono::with('user', 'credito')->where('id_user', $usuario->id_user)->get();
            if ($abonos->isEmpty()) {
                return back()->with('error', 'No se encontraron abonos para el usuario "' . $nombreUsuario . '".');
            }

            return view('abono.showAbono', ['abonos' => $abonos]);
        }

        return back()->with('error', 'Debes ingresar un ID de abono o un nombre de usuario.');
    }


    public function edit($id)
    {
        $abono = Abono::find($id);

        if (!$abono) {
            return redirect()->back()->with('error', 'El abono no se encontró.');
        }

        // Obtener todos los créditos del mismo usuario al que pertenece el abono
        $creditos = Credito::with('user')
            ->where('id_user', $abono->id_user)
            ->get();

        return view('abono.editAbono', [
            'abono' => $abono,
            'creditos' => $creditos,
        ]);
    }


    public function update(Request $request, $id)
    {
        $abono = Abono::find($id);

        if (!$abono) {
            return redirect()->route('abono.index')->with('error', 'El abono no se encontró.');
        }

        $montoAnterior = $abono->monto_abono;
        $creditoAnteriorId = $abono->id_credito;

        $nuevoMonto = $request->input('monto_abono');
        $nuevoCreditoId = $request->input('id_credito');

        // Actualizar el abono
        $abono->monto_abono = $nuevoMonto;
        $abono->id_credito = $nuevoCreditoId;
        $abono->save();

        // Ajustar el saldo del crédito anterior
        if ($creditoAnteriorId && $creditoAnteriorId != $nuevoCreditoId) {
            $creditoAnterior = Credito::find($creditoAnteriorId);
            if ($creditoAnterior) {
                $creditoAnterior->saldo_total += $montoAnterior;
                $creditoAnterior->save();
            }
        }

        // Ajustar el saldo del crédito nuevo
        $creditoNuevo = Credito::find($nuevoCreditoId);
        if ($creditoNuevo) {
            $ajuste = ($creditoAnteriorId == $nuevoCreditoId)
                        ? $montoAnterior - $nuevoMonto // solo actualizó el monto
                        : $nuevoMonto; // cambio de crédito: restar monto completo

            $creditoNuevo->saldo_total -= $ajuste;
            if ($creditoNuevo->saldo_total < 0) $creditoNuevo->saldo_total = 0;
            $creditoNuevo->save();
        }

        return redirect()->route('abono.index')->with('success', 'El abono se ha actualizado con éxito.');
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
