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

        $usuarios = $user->hasRole('administrador') ? User::all() : collect();

        return view('pedido/pedidoIndex', compact('pedidoIndex', 'usuarios'));
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
        $busqueda = $request->input('busqueda');
        $user = Auth::user();

        if (!$busqueda) {
            return back()->with('error', 'Debes ingresar un ID de pedido o un nombre de usuario.');
        }

        // Buscar por ID de pedido (numérico)
        if (is_numeric($busqueda)) {
            $pedido = Pedido::with('user')->find($busqueda);

            if (!$pedido) {
                return back()->with('error', 'El pedido no se encontró.');
            }

            // Validar si el usuario tiene acceso
            if (!$user->hasRole('administrador') && $pedido->id_user !== $user->id_user) {
                return back()->with('error', 'No tienes permiso para ver este pedido.');
            }

            return view('pedido.showPedido', ['pedidos' => collect([$pedido])]);
        }

        // Buscar por nombre de usuario (solo admin)
        if (!$user->hasRole('administrador')) {
            return back()->with('error', 'Solo puedes buscar tus propios pedidos por ID.');
        }

        // Buscar múltiples usuarios con nombre similar
        $usuarios = User::where('nombre_usuario', 'ILIKE', '%' . $busqueda . '%')->pluck('id_user');

        if ($usuarios->isEmpty()) {
            return back()->with('error', 'No se encontraron usuarios con ese nombre.');
        }

        $pedidos = Pedido::with('user')->whereIn('id_user', $usuarios)->get();

        if ($pedidos->isEmpty()) {
            return back()->with('error', 'No se encontraron pedidos para "' . $busqueda . '".');
        }

        return view('pedido.showPedido', ['pedidos' => $pedidos]);
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
        $nuevoTotal = (float) $request->input('total', $totalAnterior);

        // Si el pedido está cerrado y tiene crédito
        if ($pedido->estado_pedido == 0 && $pedido->id_credito) {
            $credito = Credito::find($pedido->id_credito);
            if ($credito && ($credito->estado == 0 || $credito->fecha_vencimiento < now())) {
                return back()->with('error', 'No puedes modificar un pedido asociado a un crédito cerrado o vencido.');
            }

            // Bloquear si el nuevo total supera $10,000
            if ($nuevoTotal > 10000) {
                return back()->with('error', 'El nuevo total supera el límite de $10,000.');
            }

            // Validar que la suma del nuevo total con los créditos activos no exceda $10,000
            $user = $pedido->user;
            $creditosActivos = Credito::where('id_user', $user->id_user)
                ->where('estado', 1)
                ->whereDate('fecha_vencimiento', '>=', now())
                ->get();

            $saldoActual = $creditosActivos->sum('saldo_total');
            $saldoRestante = $saldoActual - $totalAnterior + $nuevoTotal;

            if ($saldoRestante > 10000) {
                return back()->with('error', 'La suma de los créditos activos más el nuevo total del pedido supera $10,000.');
            }
        }

        $pedido->total_pedido = $nuevoTotal;
        $pedido->metodo_pago = $request->input('metodo_pago', $pedido->metodo_pago);
        $pedido->estado_pedido = $request->input('estado_pedido', $pedido->estado_pedido);

        // Cambiar crédito si es necesario
        if ($pedido->metodo_pago === 'contado') {
            $pedido->id_credito = null;
        } elseif ($request->filled('id_credito')) {
            $nuevoCreditoId = $request->input('id_credito');
            if ($nuevoCreditoId != $pedido->id_credito) {
                $nuevoCredito = Credito::find($nuevoCreditoId);
                if (!$nuevoCredito || $nuevoCredito->estado == 0 || $nuevoCredito->fecha_vencimiento < now()) {
                    return back()->with('error', 'No puedes asignar un crédito cerrado o vencido.');
                }

                $pedido->id_credito = $nuevoCreditoId;
            }
        }

        $pedido->save();

        // Si el pedido ya estaba cerrado y es a crédito, ajustar saldo
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
            // Si cambió de crédito, recalcular ambos
            if ($creditoAnteriorId != $pedido->id_credito) {
                $this->recalcularSaldoCredito($creditoAnteriorId);
                $this->recalcularSaldoCredito($pedido->id_credito);
            }
        }

        $pedido->user->evaluarNivelUsuario();

        return redirect()->route('pedido.index')->with('success', 'El pedido se ha actualizado con éxito.');
    }




    public function cerrar(Request $request, $id_pedido)
    {
        $pedido = Pedido::findOrFail($id_pedido);

        if ($pedido->estado_pedido == 0) {
            return back()->with('error', 'El pedido ya está cerrado.');
        }

        $user = $pedido->user;

        if ($user->tienePagosAtrasadosSinAbonar()) {
            return back()->with('error', 'No puedes cerrar el pedido porque tienes pagos vencidos sin abonar.');
        }

        $metodo = $request->input('metodo_pago');
        $total = (float) $request->input('total');
        $id_credito_nuevo = $request->input('id_credito');
        $id_credito_anterior = $pedido->id_credito;
        $total_anterior = $pedido->total_pedido;

        // Validar monto del pedido
        if ($total > 10000) {
            return back()->with('error', 'El total del pedido supera el límite permitido de $10,000.');
        }

        $creditosUsuario = Credito::where('id_user', $pedido->id_user)->get();
        $deudaTotal = $creditosUsuario->sum('saldo_total');

        // Validar límites de crédito al cerrar
        if ($metodo === 'credito' && ($deudaTotal + $total - $total_anterior) > 10000) {
            return back()->with('error', 'No puedes cerrar el pedido. La suma de tus deudas superaría los $10,000.');
        }

        // Si el crédito anterior existe y se cambia a contado o a otro crédito, ajustar el saldo anterior
        if ($id_credito_anterior && ($metodo !== 'credito' || $id_credito_anterior != $id_credito_nuevo)) {
            $creditoAnterior = Credito::find($id_credito_anterior);
            if ($creditoAnterior) {
                $creditoAnterior->saldo_total = max(0, $creditoAnterior->saldo_total - $total_anterior);
                $creditoAnterior->save();
            }
        }

        $user->evaluarNivelUsuario(); 

        if ($metodo === 'credito') {
            $esNuevoCredito = empty($id_credito_nuevo);

            if (!$esNuevoCredito) {
                // Usar crédito existente
                $credito = Credito::find($id_credito_nuevo);

                if (!$credito || $credito->estado == 0 || $credito->fecha_vencimiento < now()) {
                    return back()->with('error', 'El crédito seleccionado no es válido.');
                }

                // ✅ Solo sumar diferencia si es el mismo crédito
                if ($id_credito_nuevo == $id_credito_anterior) {
                    $credito->saldo_total = $credito->saldo_total - $total_anterior + $total;
                } else {
                    $credito->saldo_total += $total;
                }

                $credito->save();
                $pedido->id_credito = $credito->id_credito;
            } else {
                // Crear nuevo crédito solo si tiene menos de 3 activos
                $creditosActivos = $creditosUsuario->filter(function ($c) {
                return $c->estado == 1 && $c->fecha_vencimiento >= now();
                });

                $creditosVencidos = $creditosUsuario->filter(function ($c) {
                    return $c->estado == 1 && $c->fecha_vencimiento < now();
                });


                if ($creditosActivos->count() >= 3) {
                    return back()->with('error', 'Ya tienes 3 créditos activos. No puedes crear uno nuevo.');
                }

                $nuevoCredito = Credito::create([
                    'id_user' => $user->id_user,
                    'saldo_total' => $total,
                    'fecha_liquidacion' => null,
                    'fecha_vencimiento' => now()->addDays($user->dias_aplazo)->endOfDay(),
                    'estado' => 1,
                ]);

                $pedido->id_credito = $nuevoCredito->id_credito;
            }

            $pedido->metodo_pago = 'credito';
        } elseif ($metodo === 'contado') {
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
        $pedido->user->evaluarNivelUsuario();

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

    private function puedeCerrarAPedidoACredito(Pedido $pedido, $montoNuevo = null, $esNuevoCredito = false)
    {
        $user = $pedido->user;

        $creditosActivos = Credito::where('id_user', $user->id_user)
            ->where('estado', 1)
            ->whereDate('fecha_vencimiento', '>=', now()) // No vencidos
            ->get();

        // Si alguno está vencido bloquea
        if ($creditosActivos->contains(fn($c) => $c->fecha_vencimiento < now())) {
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
        } else {
            // Si no hay crédito seleccionado, se intenta crear uno nuevo
            if ($esNuevoCredito && $creditosActivos->count() >= 3) {
                return false;
            }
            return $montoNuevo <= 10000;
        }
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
