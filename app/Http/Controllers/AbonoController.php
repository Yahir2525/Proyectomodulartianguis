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

        $usuarios = $user->hasRole('administrador') ? User::all() : collect();

        return view('abono/abonoIndex', compact('abonoIndex', 'usuarios'));
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
        // Validar entrada
        $request->validate([
            'id_user' => 'required|exists:users,id_user',
            'id_credito' => 'required|exists:creditos,id_credito',
            'monto_abono' => 'required|numeric|min:0.01',
        ]);

        $credito = Credito::find($request->input('id_credito'));

        // Verificar que el crédito esté activo
        if (!$credito || $credito->estado == 0) {
            return back()->with('error', 'No se puede abonar a un crédito cerrado o inexistente.');
        }

        // Verificar que el saldo no sea cero
        if ($credito->saldo_total <= 0) {
            return back()->with('error', 'El crédito ya está liquidado, no se puede abonar más.');
        }

        $montoSolicitado = (float) $request->input('monto_abono');
        $montoFinal = min($montoSolicitado, $credito->saldo_total); // limitar abono al máximo permitido

        // Crear el abono con monto ajustado
        $abono = new Abono();
        $abono->id_credito = $credito->id_credito;
        $abono->id_user = $request->input('id_user');
        $abono->monto_abono = $montoFinal;
        $abono->save();

        // Actualizar saldo del crédito
        $credito->saldo_total -= $montoFinal;
        if ($credito->saldo_total <= 0) {
            $credito->saldo_total = 0;
            $credito->estado = 0;
            $credito->fecha_liquidacion = now();
        }

        $credito->save();

        $mensaje = 'Abono registrado por $' . number_format($montoFinal, 2);
        if ($montoSolicitado > $montoFinal) {
            $mensaje .= ' (ajustado del monto ingresado $' . number_format($montoSolicitado, 2) . ' debido a saldo insuficiente del crédito).';
        }

        $user = $credito->user;
        $user->evaluarNivelUsuario();

        return redirect()->route('abono.index')->with('success', $mensaje);
    }

    public function show(Request $request)
    {
        $busqueda = $request->input('busqueda');
        $user = Auth::user();

        if (!$busqueda) {
            return back()->with('error', 'Debes ingresar un ID de abono o un nombre de usuario.');
        }

        // Si es numérico, se interpreta como ID de abono
        if (is_numeric($busqueda)) {
            if ($user->hasRole('administrador')) {
                $abono = Abono::with(['user', 'credito'])->find($busqueda);
            } else {
                $abono = Abono::with(['user', 'credito'])
                    ->where('id_abono', $busqueda)
                    ->where('id_user', $user->id_user)
                    ->first();
            }

            if (!$abono) {
                return back()->with('error', 'El abono no se encontró o no te pertenece.');
            }

            return view('abono.showAbono', ['abonos' => collect([$abono])]);
        }

        // Si no es número, se asume búsqueda por nombre (solo para admin)
        // Si no es número, se asume búsqueda por nombre (solo para admin)
        if ($user->hasRole('administrador')) {
            $usuario = User::where('nombre_usuario', 'ILIKE', '%' . $busqueda . '%')->get();

            if (!$usuario) {
                return back()->with('error', 'Usuario no encontrado.');
            }
            
            $abonos = Abono::with('user')->whereIn('id_user', $usuario->pluck('id_user'))->get();

            if ($abonos->isEmpty()) {
                return back()->with('error', 'No se encontraron abonos para el usuario "' . $busqueda . '".');
            }

            return view('abono.showAbono', ['abonos' => $abonos]);
        }


        return back()->with('error', 'Solo puedes buscar tus abonos por ID.');
    }



    public function edit($id)
    {
        $abono = Abono::find($id);

        if (!$abono) {
            return redirect()->back()->with('error', 'El abono no se encontró.');
        }

        // Créditos activos del usuario
        $creditos = Credito::with('user')
            ->where('id_user', $abono->id_user)
            ->where('estado', 1)
            ->get();

        // Obtener el crédito asociado al abono, aunque esté cerrado
        $creditoActual = Credito::find($abono->id_credito);

        // Si no está en la lista (porque está cerrado), agregarlo manualmente
        if ($creditoActual && !$creditos->contains('id_credito', $creditoActual->id_credito)) {
            $creditos->push($creditoActual);
        }

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

        $request->validate([
            'monto_abono' => 'required|numeric|min:0.01',
            'id_credito' => 'required|exists:creditos,id_credito',
        ]);

        $nuevoMonto = (float) $request->input('monto_abono');
        $nuevoCreditoId = $request->input('id_credito');
        $montoAnterior = $abono->monto_abono;
        $creditoAnteriorId = $abono->id_credito;

        $creditoNuevo = Credito::find($nuevoCreditoId);
        if (!$creditoNuevo) {
            return redirect()->route('abono.index')->with('error', 'El crédito no existe.');
        }

        // Revertir al crédito anterior si cambió
        if ($creditoAnteriorId && $creditoAnteriorId != $nuevoCreditoId) {
            $creditoAnterior = Credito::find($creditoAnteriorId);
            if ($creditoAnterior) {
                $creditoAnterior->saldo_total += $montoAnterior;

                // Reactivar si estaba cerrado
                if ($creditoAnterior->estado == 0 && $creditoAnterior->saldo_total > 0) {
                    $creditoAnterior->estado = 1;
                    $creditoAnterior->fecha_liquidacion = null;
                }

                $creditoAnterior->save();
            }

            // Se va a descontar el nuevo monto completo del nuevo crédito
            $ajuste = $nuevoMonto;
        } else {
            // Si no cambió de crédito, aplicar la diferencia (puede ser positiva o negativa)
            $ajuste = $nuevoMonto - $montoAnterior;
        }

        // Ajuste real que se aplicará al crédito nuevo (sin pasarse del saldo)
        $montoFinal = $ajuste > 0
            ? min($ajuste, $creditoNuevo->saldo_total)
            : $ajuste;

        // Actualizar el abono (con tope si excede el saldo + monto anterior)
        $abono->monto_abono = $nuevoMonto > ($creditoNuevo->saldo_total + $montoAnterior)
            ? $creditoNuevo->saldo_total + $montoAnterior
            : $nuevoMonto;

        $abono->id_credito = $nuevoCreditoId;
        $abono->save();

        // Aplicar ajuste al nuevo crédito
        $creditoNuevo->saldo_total -= $montoFinal;

        if ($creditoNuevo->saldo_total > 0) {
            $creditoNuevo->estado = 1;
            $creditoNuevo->fecha_liquidacion = null;
        }

        // LIQUIDAR si saldo llegó a 0
        if ($creditoNuevo->saldo_total <= 0) {
            $creditoNuevo->saldo_total = 0;
            $creditoNuevo->estado = 0;
            $creditoNuevo->fecha_liquidacion = now();
        }

        $creditoNuevo->save();

        $mensaje = 'El abono se ha actualizado correctamente.';
        if ($ajuste > $montoFinal) {
            $mensaje .= ' Se ajustó automáticamente al saldo disponible del crédito.';
        }

        $user = $creditoNuevo->user;
        $user->evaluarNivelUsuario();
        
        return redirect()->route('abono.index')->with('success', $mensaje);

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

        $user = $credito->user;
        $user->evaluarNivelUsuario();

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

        if ($credito->saldo_total == 0) {
            $credito->estado = 0;
            $credito->fecha_liquidacion = now();
        } else {
            $credito->estado = 1;
            $credito->fecha_liquidacion = null;
        }

        $credito->save();

        return redirect()->back()->with('success', 'Monto del abono aplicado al crédito correctamente.');
    }

    // private function actualizarNivelUsuarioPorHistorial($id_user)
    // {
    //     $user = User::find($id_user);
    //     $creditos = Credito::where('id_user', $id_user)
    //         ->whereNotNull('fecha_liquidacion')
    //         ->whereNotNull('fecha_vencimiento')
    //         ->get();

    //     $aTiempo = 0;
    //     $tarde = 0;

    //     foreach ($creditos as $credito) {
    //         if ($credito->fecha_liquidacion <= $credito->fecha_vencimiento) {
    //             $aTiempo++;
    //         } else {
    //             $tarde++;
    //         }
    //     }

    //     if ($aTiempo >= 3 && $tarde == 0) {
    //         $user->nivel_usuario = 'excelente';
    //     } elseif ($aTiempo >= $tarde) {
    //         $user->nivel_usuario = 'bueno';
    //     } else {
    //         $user->nivel_usuario = 'malo';
    //     }

    //     $user->save();
    // }



}
