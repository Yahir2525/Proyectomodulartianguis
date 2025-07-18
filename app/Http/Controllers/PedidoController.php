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
        $pedido = Pedido::find($id);
        if (!$pedido) {
            return redirect()->back()->with('error', 'El pedido no se encontró.');
        }
        return view('/pedido/showPedido', ['pedido' => $pedido]);
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

        // Actualizar campos del pedido
        $pedido->total_pedido = $request->input('total', $pedido->total_pedido);
        $pedido->metodo_pago = $request->input('metodo_pago', $pedido->metodo_pago);
        $pedido->estado_pedido = $request->input('estado_pedido', $pedido->estado_pedido);

        if ($pedido->metodo_pago === 'contado') {
            $pedido->id_credito = null;
        } elseif ($request->filled('id_credito')) {
            $pedido->id_credito = $request->input('id_credito');
        }

        $pedido->save();

        // Recalcular saldo de créditos involucrados
        $this->recalcularSaldoCredito($creditoAnteriorId);
        $this->recalcularSaldoCredito($pedido->id_credito);

        return redirect()->route('pedido.index')->with('success', 'El pedido se ha actualizado con éxito.');
    }


    public function cerrar(Request $request, $id_pedido)
    {
        $pedido = Pedido::findOrFail($id_pedido);

        // Actualiza total
        if ($request->has('total')) {
            $pedido->total_pedido = $request->input('total');
        }

        $metodo = $request->input('metodo_pago');

        if ($metodo === 'contado') {
            $pedido->metodo_pago = 'contado';
            $pedido->estado_pedido = 0;
            $pedido->id_credito = null; // Quita el crédito asignado
            $pedido->save();

            return back()->with('success', 'Pedido cerrado como contado.');
        }

        if ($metodo === 'credito') {
            if ($request->filled('id_credito')) {
                // Crédito existente
                $pedido->id_credito = $request->input('id_credito');
            } else {
                // Crear nuevo crédito sin pedir fechas en formulario, fechas automáticas aquí
                $fechaCreacion = now();
                $fechaVencimiento = $fechaCreacion->copy()->addDays(60); // máximo 60 días después

                $nuevoCredito = Credito::create([
                    'id_user' => $pedido->id_user,
                    'saldo_total' => $pedido->total_pedido,
                    'fecha_liquidacion' => null, // será asignada cuando se liquide el crédito
                    'fecha_vencimiento' => $fechaVencimiento,
                    'estado' => 1,
                ]);

                $pedido->id_credito = $nuevoCredito->id_credito;
            }

            $pedido->metodo_pago = 'credito';
            $pedido->estado_pedido = 0;
            $pedido->save();

            $this->recalcularSaldoCredito($pedido->id_credito);

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
