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

        // Crear el abono
        $abono = new Abono();
        $abono->id_credito = $credito->id_credito;
        $abono->id_user = $request->input('id_user');
        $abono->monto_abono = $request->input('monto_abono');
        $abono->save();

        // Actualizar saldo del crédito
        $credito->saldo_total -= $abono->monto_abono;
        if ($credito->saldo_total < 0) {
            $credito->saldo_total = 0;
        }

        // Si se liquida el crédito
        if ($credito->saldo_total == 0) {
            $credito->estado = 0;
            $credito->fecha_liquidacion = now();
        }

        $credito->save();

        return redirect()->route('abono.index')->with('success', 'Abono registrado y aplicado correctamente.');
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

        // Solo créditos activos
        $creditos = Credito::with('user')
            ->where('id_user', $abono->id_user)
            ->where('estado', 1) // 👈 solo créditos activos
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

        $nuevoMonto = $request->input('monto_abono');
        $nuevoCreditoId = $request->input('id_credito');

        // Validar que el nuevo crédito exista y esté activo
        $creditoNuevo = Credito::find($nuevoCreditoId);
        if (!$creditoNuevo || $creditoNuevo->estado == 0) {
            return redirect()->route('abono.index')->with('error', 'No se puede aplicar el abono a un crédito cerrado.');
        }

        $montoAnterior = $abono->monto_abono;
        $creditoAnteriorId = $abono->id_credito;

        // Actualizar el abono con nuevo monto y/o crédito
        $abono->monto_abono = $nuevoMonto;
        $abono->id_credito = $nuevoCreditoId;
        $abono->save();

        // Revertir saldo al crédito anterior si cambió
        if ($creditoAnteriorId && $creditoAnteriorId != $nuevoCreditoId) {
            $creditoAnterior = Credito::find($creditoAnteriorId);
            if ($creditoAnterior) {
                $creditoAnterior->saldo_total += $montoAnterior;
                $creditoAnterior->save();
            }
        }

        // Calcular ajuste al crédito nuevo
        $ajuste = ($creditoAnteriorId == $nuevoCreditoId)
                    ? $montoAnterior - $nuevoMonto
                    : $nuevoMonto;

        $creditoNuevo->saldo_total -= $ajuste;

        // Evitar saldo negativo
        if ($creditoNuevo->saldo_total < 0) {
            $creditoNuevo->saldo_total = 0;
        }

        // Si se liquida el crédito
        if ($creditoNuevo->saldo_total == 0) {
            $creditoNuevo->estado = 0;
            $creditoNuevo->fecha_liquidacion = now(); // fecha actual como fecha de liquidación
        }

        $creditoNuevo->save();

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
