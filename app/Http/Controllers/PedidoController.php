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

class PedidoController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('administrador')) {
            $pedidoIndex = Pedido::all();
        } else {
            $pedidoIndex = Pedido::where('id_user', $user->id_user)->get();
        }

        return view('pedido/pedidoIndex', compact('pedidoIndex'));
    }


    public function create()
    {
        $usuario = Auth::user();

        if ($usuario->hasRole('administrador')) {
            // Obtener todos los usuarios para mostrar en el select
            $usuarios = User::all();
        } else {
            $usuarios = null; // No se muestra select para usuarios normales
        }

        return view('pedido.createPedido', compact('usuarios', 'usuario'));
    }


    public function store(Request $request)
    {
        $userId = $request->input('id_user');

        $pedido = new Pedido();
        $pedido->id_user = $userId;
        $pedido->id_credito = $request->input("id_credito");
        $pedido->estado_pedido = 1;
        $pedido->metodo_pago = '';

        if ($pedido->save()) {
            return redirect('/pedido')->with('pedido_reciente', $pedido->id_pedido);
        }
    }

    public function show(Request $request)
    {
        $id = $request->input('id_pedido');
        $nombreUsuario = $request->input('nombre_usuario');

        // Buscar por ID de pedido
        if ($id) {
            $pedido = Pedido::with('user')->find($id);
            if (!$pedido) {
                return back()->with('error', 'El pedido no se encontró.');
            }
            return view('pedido.showPedido', ['pedidos' => collect([$pedido])]);
        }

        // Buscar por nombre de usuario
        if ($nombreUsuario) {
            $usuario = User::where('nombre_usuario', 'ILIKE', $nombreUsuario)->first();
            if (!$usuario) {
                return back()->with('error', 'Usuario no encontrado.');
            }

            $pedidos = Pedido::with('user')->where('id_user', $usuario->id_user)->get();
            if ($pedidos->isEmpty()) {
                return back()->with('error', 'No se encontraron pedidos para el usuario "' . $nombreUsuario . '".');
            }

            return view('pedido.showPedido', ['pedidos' => $pedidos]);
        }

        return back()->with('error', 'Debes ingresar un ID de pedido o un nombre de usuario.');
    }


    public function edit($id)
    {
        $pedido = Pedido::find($id);

        if (!$pedido) {
            return redirect()->route('pedido.index')->with('error', 'El pedido no se encontró.');
        }

        $creditos = Credito::where('id_user', $pedido->id_user)->get();

        return view('pedido.editPedido', compact('pedido', 'creditos'));
    }

    private function recalcularSaldoCredito($id_credito)
    {
        if ($id_credito) {
            $credito = Credito::find($id_credito);
            if ($credito) {
                $totalPedidos = Pedido::where('id_credito', $id_credito)->sum('total_pedido');
                $totalAbonos = Abono::where('id_credito', $id_credito)->sum('monto_abono');

                $credito->saldo_total = max($totalPedidos - $totalAbonos, 0);
                $credito->save();
            }
        }
    }



    public function update(Request $request, Pedido $pedido)
    {
        $pedido = Pedido::find($pedido->id_pedido);
        $creditoAnteriorId = $pedido->id_credito;
        $totalAnterior = $pedido->total_pedido;

        $nuevoTotal = $request->input('total', $totalAnterior);

        // Validar que no se aumente el total si el pedido está cerrado y tiene crédito vencido o cerrado
        if ($pedido->estado_pedido == 0 && $pedido->id_credito) {
            $credito = Credito::find($pedido->id_credito);
            if ($credito && ($credito->estado == 0 || $credito->fecha_vencimiento < now())) {
                return back()->with('error', 'No puedes modificar un pedido asociado a un crédito cerrado o vencido.');
            }
        }

        // Validar límites de crédito y cantidad de créditos activos
        if ($pedido->estado_pedido == 0 && !$this->validarCreditoAlModificar($pedido, $nuevoTotal)) {
            return back()->with('error', 'No se puede modificar el pedido porque el crédito superaría los $10,000 o el usuario tiene más de 3 créditos activos.');
        }

        $pedido->total_pedido = $nuevoTotal;
        $pedido->metodo_pago = $request->input('metodo_pago', $pedido->metodo_pago);
        $pedido->estado_pedido = $request->input('estado_pedido', $pedido->estado_pedido);

        if ($pedido->metodo_pago === 'contado') {
            $pedido->id_credito = null;
        } elseif ($request->filled('id_credito')) {
            $pedido->id_credito = $request->input('id_credito');
        }

        $pedido->save();

        if ($pedido->estado_pedido == 0 && $pedido->metodo_pago === 'credito' && $pedido->id_credito) {
            $diferencia = $nuevoTotal - $totalAnterior;
            if ($diferencia != 0) {
                $credito = Credito::find($pedido->id_credito);
                if ($credito) {
                    $credito->saldo_total += $diferencia;
                    $credito->save();
                }
            }
        } else {
            // Solo recalcular si cambió de crédito
            if ($creditoAnteriorId != $pedido->id_credito) {
                $this->recalcularSaldoCredito($creditoAnteriorId);
                $this->recalcularSaldoCredito($pedido->id_credito);
            }
        }

        return redirect()->route('pedido.index')->with('success', 'El pedido se ha actualizado con éxito.');
    }



    public function cerrar(Request $request, $id_pedido)
    {
        $pedido = Pedido::findOrFail($id_pedido);

        if ($pedido->estado_pedido == 0) {
            return back()->with('error', 'El pedido ya está cerrado.');
        }

        $metodo = $request->input('metodo_pago');
        $total = (float) $request->input('total');
        $id_credito_nuevo = $request->input('id_credito');
        $id_credito_anterior = $pedido->id_credito;
        $total_anterior = $pedido->total_pedido;

        // Quitar saldo del crédito anterior si cambia de crédito o cambia a contado
        if ($id_credito_anterior && ($metodo !== 'credito' || $id_credito_anterior != $id_credito_nuevo)) {
            $creditoAnterior = Credito::find($id_credito_anterior);
            if ($creditoAnterior) {
                $creditoAnterior->saldo_total = max(0, $creditoAnterior->saldo_total - $total_anterior);
                $creditoAnterior->save();
            }
        }

        if ($metodo === 'credito') {
            $userId = $pedido->id_user;

            if ($id_credito_nuevo) {
                // Validar crédito existente
                $credito = Credito::find($id_credito_nuevo);

                if (!$credito || $credito->estado == 0 || $credito->fecha_vencimiento < now()) {
                    return back()->with('error', 'El crédito seleccionado no es válido.');
                }

                if (($credito->saldo_total + $total) > 10000) {
                    return back()->with('error', 'El saldo del crédito superaría los $10,000 con este pedido.');
                }

                $credito->saldo_total += $total;
                $credito->save();
                $pedido->id_credito = $credito->id_credito;
            } else {
                // Crear nuevo crédito: validar límite de créditos activos
                $creditosActivos = Credito::where('id_user', $userId)
                    ->where('estado', 1)
                    ->whereDate('fecha_vencimiento', '>=', now())
                    ->get();

                if ($creditosActivos->count() >= 3) {
                    return back()->with('error', 'No puedes crear un nuevo crédito porque ya tienes 3 créditos activos.');
                }

                if ($total > 10000) {
                    return back()->with('error', 'El monto del nuevo crédito supera el límite de $10,000.');
                }

                $nuevoCredito = Credito::create([
                    'id_user' => $userId,
                    'saldo_total' => $total,
                    'fecha_liquidacion' => null,
                    'fecha_vencimiento' => now()->addDays(60),
                    'estado' => 1,
                ]);

                $pedido->id_credito = $nuevoCredito->id_credito;
            }

            $pedido->metodo_pago = 'credito';
        }

        elseif ($metodo === 'contado') {
            $pedido->metodo_pago = 'contado';
            $pedido->id_credito = null;
        }

        $pedido->estado_pedido = 0;
        $pedido->total_pedido = $total;
        $pedido->save();

        return redirect()->route('pedido.index')->with('success', 'Pedido cerrado correctamente.');
    }





    public function reabrir(Request $request, $id_pedido)
    {
        $pedido = Pedido::findOrFail($id_pedido);

        // Si el pedido tiene un crédito asociado, verificar que no esté cerrado
        if ($pedido->id_credito) {
            $credito = Credito::find($pedido->id_credito);

            if ($credito && $credito->estado == 0) {
                return redirect()->back()->with('error', 'No se puede reabrir el pedido porque está asociado a un crédito cerrado.');
            }
        }

        // Guardamos el total antes de abrir
        session()->put("total_anterior_pedido_{$pedido->id_pedido}", $pedido->total_pedido);

        $pedido->estado_pedido = 1;
        $pedido->save();

        return redirect()->back()->with('success', 'Pedido reabierto.');
    }




    public function destroy(Pedido $pedido)
    {
        $pedido = Pedido::find($pedido->id_pedido);

        if (!$pedido) {
            return redirect()->route('pedido.index')->with('error', 'El pedido no se encontró.');
        }

        $idCredito = $pedido->id_credito;
        $totalPedido = $pedido->total_pedido;

        $pedido->delete();

        // Recalcular saldo del crédito si aplica
        if ($idCredito) {
            $credito = Credito::find($idCredito);
            if ($credito) {
                // Sumar todos los totales de pedidos restantes de ese crédito
                $totalPedidos = Pedido::where('id_credito', $idCredito)->sum('total_pedido');
                $credito->saldo_total = $totalPedidos;
                $credito->save();
            }
        }

        $this->recalcularSaldoCredito($idCredito);

        return redirect()->route('pedido.index')->with('success', 'El pedido se ha eliminado con éxito.');
    }

    private function usuarioBloqueadoParaCredito($idUser)
    {
        $creditosActivos = Credito::where('id_user', $idUser)
                                ->where('estado', 1)
                                ->get();

        if ($creditosActivos->count() >= 3) {
            return true;
        }

        if ($creditosActivos->sum('saldo_total') > 10000) {
            return true;
        }

        return false;
    }

    // private function puedeCerrarAPedidoACredito(Pedido $pedido, $montoNuevo = null)
    // {
    //     $user = $pedido->user;

    //     $creditosActivos = Credito::where('id_user', $user->id_user)->where('estado', 1)->get();
    //     if ($creditosActivos->count() >= 3) {
    //         return false;
    //     }

    //     if ($montoNuevo === null) {
    //         $montoNuevo = $pedido->total_pedido;
    //     }

    //     $idCredito = request()->input('id_credito');
    //     if ($idCredito) {
    //         $credito = Credito::find($idCredito);
    //         if (!$credito) return false;
    //         $nuevoSaldo = $credito->saldo_total + $montoNuevo;
    //         return $nuevoSaldo <= 10000;
    //     }

    //     return $montoNuevo <= 10000;
    // }


    private function puedeCerrarAPedidoACredito(Pedido $pedido, $montoNuevo = null)
    {
        $user = $pedido->user;

        $creditosActivos = Credito::where('id_user', $user->id_user)
            ->where('estado', 1)
            ->whereDate('fecha_vencimiento', '>=', now()) // No vencidos
            ->get();

        // Si alguno ya está vencido, se bloquea
        if ($creditosActivos->contains(fn($c) => $c->fecha_vencimiento < now())) {
            return false;
        }

        if ($creditosActivos->count() >= 3) {
            return false;
        }

        $montoNuevo ??= $pedido->total_pedido;

        $idCredito = request()->input('id_credito');
        if ($idCredito) {
            $credito = Credito::find($idCredito);
            if (!$credito || $credito->estado == 0 || $credito->fecha_vencimiento < now()) {
                return false;
            }

            $nuevoSaldo = $credito->saldo_total + $montoNuevo;
            return $nuevoSaldo <= 10000;
        }

        return $montoNuevo <= 10000;
    }

    private function validarCreditoAlModificar(Pedido $pedido, $nuevoTotal, $esNuevoCredito = false)
    {
        if ($pedido->metodo_pago !== 'credito' || !$pedido->id_credito) {
            return true;
        }

        $user = $pedido->user;

        $creditosActivos = Credito::where('id_user', $user->id_user)
            ->where('estado', 1)
            ->whereDate('fecha_vencimiento', '>=', now())
            ->get();

        // Solo si se va a crear un nuevo crédito aplicamos la regla de máximo 3 créditos activos
        if ($esNuevoCredito && $creditosActivos->count() >= 3) {
            return false;
        }

        $credito = Credito::find($pedido->id_credito);
        if (!$credito) return false;

        $nuevoSaldo = $credito->saldo_total + ($nuevoTotal - $pedido->total_pedido);

        // Validar que no supere $10,000
        return $nuevoSaldo <= 10000;
    }



}
