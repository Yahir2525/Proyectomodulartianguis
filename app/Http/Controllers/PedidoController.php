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
        $usuarioId = Auth::id();
        return view('pedido/createPedido', compact ('usuarioId'));
    }

    public function store(Request $request)
    {
        $userId = $request->input('id_user');

        $pedido = new Pedido();
        $pedido->id_user = $userId;
        $pedido->id_credito = $request->input("id_credito");
        $pedido->estado_pedido = 1;
        $pedido->metodo_pago = 'contado';

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
                $credito->saldo_total = $totalPedidos;
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

        $totalAnterior = $pedido->total_pedido;

        // Si viene un nuevo total desde la vista, se aplica
        if ($request->has('total')) {
            $pedido->total_pedido = $request->input('total');
        }

        $metodo = $request->input('metodo_pago');
        $creditoAnteriorId = $pedido->id_credito;
        $nuevoCredito = null;

        if ($metodo === 'contado') {
            $pedido->metodo_pago = 'contado';
            $pedido->estado_pedido = 0;
            $pedido->id_credito = null;
            $pedido->save();

            // Recalcula el saldo del crédito anterior si existía
            $this->recalcularSaldoCredito($creditoAnteriorId);

            return back()->with('success', 'Pedido cerrado como contado.');
        }

        if ($metodo === 'credito') {
            // Asignar crédito existente o crear uno nuevo
            if ($request->filled('id_credito')) {
                $pedido->id_credito = $request->input('id_credito');
            } else {
                $fechaVencimiento = now()->addDays(60);
                $nuevoCredito = Credito::create([
                    'id_user' => $pedido->id_user,
                    'saldo_total' => 0, // Se ajustará abajo
                    'fecha_liquidacion' => null,
                    'fecha_vencimiento' => $fechaVencimiento,
                    'estado' => 1,
                ]);
                $pedido->id_credito = $nuevoCredito->id_credito;
            }

            $pedido->metodo_pago = 'credito';
            $pedido->estado_pedido = 0;
            $pedido->save();

            if ($nuevoCredito) {
                // Es un nuevo crédito: simplemente asigna el total actual del pedido
                $nuevoCredito->saldo_total = $pedido->total_pedido;
                $nuevoCredito->save();
            } else {
                // Crédito existente: solo actualiza con la diferencia
                $diferencia = $pedido->total_pedido - $totalAnterior;
                if ($diferencia != 0) {
                    $credito = Credito::find($pedido->id_credito);
                    if ($credito) {
                        $credito->saldo_total += $diferencia;
                        $credito->save();
                    }
                }
            }

            // Si se cambió de crédito, recalcular el anterior
            if ($creditoAnteriorId != $pedido->id_credito) {
                $this->recalcularSaldoCredito($creditoAnteriorId);
            }

            return back()->with('success', 'Pedido cerrado con crédito.');
        }

        return back()->with('error', 'Método de pago no válido.');
    }


    public function reabrir(Request $request, $id_pedido)
    {
        $pedido = Pedido::findOrFail($id_pedido);
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

        $creditoId = $pedido->id_credito;
        $pedido->delete();

        $this->recalcularSaldoCredito($creditoId);

        return redirect()->route('pedido.index')->with('success', 'El pedido se ha eliminado con éxito.');
    }
}
