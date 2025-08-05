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

class CreditoController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('administrador')) {
            $creditoIndex = Credito::all();
        } else {
            $creditoIndex = Credito::where('id_user', $user->id_user)->get(); // solo los del usuario
        }

        $usuarios = $user->hasRole('administrador') ? User::all() : collect();

        return view('credito/creditoIndex', compact('creditoIndex', 'usuarios'));
    }
    public function create()
    {
        $usuarios = User::all();

        $datosRestricciones = [];

        foreach ($usuarios as $usuario) {
            $creditos = Credito::where('id_user', $usuario->id_user)
                ->where('estado', 1);

            $datosRestricciones[$usuario->id_user] = [
                'activos' => $creditos->count(),
                'suma' => $creditos->sum('saldo_total')
            ];
        }

        return view('credito.createCredito', compact('usuarios', 'datosRestricciones'));
    }

    public function store(Request $request)
    {
        $userId = $request->input('id_user') ?? Auth::id();

        $creditosActivos = Credito::where('id_user', $userId)
            ->where('estado', 1);

        $cantidadActivos = $creditosActivos->count();
        $sumaSaldos = $creditosActivos->sum('saldo_total');

        if ($cantidadActivos >= 3) {
            return redirect()->back()->withErrors([
                'Este usuario ya tiene 3 créditos activos.'
            ]);
        }

        if ($sumaSaldos >= 10000) {
            return redirect()->back()->withErrors([
                'La suma total de los créditos activos de este usuario supera los $10,000.'
            ]);
        }

        
        $user = User::find($userId);
        $diasAplazo = $user ? $user->dias_aplazo : 0;

        $fechaVencimiento = now()->addDays($diasAplazo)->endOfDay();;


        $credito = new Credito();
        $credito->id_user = $userId;
        $credito->fecha_liquidacion = null;
        $credito->fecha_vencimiento = $fechaVencimiento;
        $credito->estado = 1;
        $credito->saldo_total = 0;

        if ($credito->save()) {
            return redirect('/credito')->with('success', 'Crédito registrado correctamente.');
        } else {
            return redirect()->back()->withErrors([
                'Error al guardar el crédito. Por favor, intenta de nuevo.'
            ]);
        }
    }

    public function crearDesdePedido(Request $request, Pedido $pedido)
    {
        $pedido = Pedido::find($pedido->id_pedido);
        if (!$pedido) {
            return back()->with('error', 'Pedido no encontrado.');
        }

        if ($pedido->id_credito) {
            return back()->with('error', 'Este pedido ya tiene un crédito asignado.');
        }

        $userId = $request->input('id_user');

        $creditosUsuario = Credito::where('id_user', $userId)->get();

        $creditosActivos = $creditosUsuario->filter(function ($c) {
            return $c->estado == 1 && $c->fecha_vencimiento >= now();
        });

        $creditosVencidos = $creditosUsuario->filter(function ($c) {
            return $c->estado == 1 && $c->fecha_vencimiento < now();
        });

        if ($creditosActivos->count() >= 3 || $creditosVencidos->count() > 0) {
            return back()->with('error', 'El usuario no puede crear un nuevo crédito: ya tiene 3 créditos activos o créditos vencidos.');
        }

        $saldoTotalActivos = $creditosActivos->sum('saldo_total');
        $nuevoSaldo = $request->input('total', 0);

        if (($saldoTotalActivos + $nuevoSaldo) > 10000) {
            return back()->with('error', 'La suma de los saldos activos más el nuevo crédito supera los $10,000.');
        }

        $user = User::find($userId);
        $diasAplazo = $user ? $user->dias_aplazo : 0;

        $fechaVencimiento = now()->addDays($diasAplazo)->endOfDay();;

        // Crear crédito
        $credito = new Credito();
        $credito->id_user = $userId;
        $credito->saldo_total = $nuevoSaldo;
        $credito->fecha_liquidacion = null;
        $credito->fecha_vencimiento = $fechaVencimiento;
        $credito->estado = 1;
        $credito->save();

        // Asignar crédito al pedido
        $pedido->id_credito = $credito->id_credito;
        $pedido->save();

        return redirect('/credito')->with('success', 'Crédito registrado correctamente.');
    }


    public function show(Request $request)
    {
        $busqueda = $request->input('busqueda');
        $user = Auth::user();

        if (!$busqueda) {
            return back()->with('error', 'Debes ingresar un ID de crédito o un nombre de usuario.');
        }

        // Si es un número, se interpreta como ID de crédito
        if (is_numeric($busqueda)) {
            if ($user->hasRole('administrador')) {
                // Administrador puede ver cualquier crédito
                $credito = Credito::with('user')->find($busqueda);
            } else {
                // Usuario normal solo puede ver sus propios créditos
                $credito = Credito::with('user')
                    ->where('id_credito', $busqueda)
                    ->where('id_user', $user->id_user)
                    ->first();
            }

            if (!$credito) {
                return back()->with('error', 'El crédito no se encontró o no te pertenece.');
            }

            // Retornamos vista con solo ese crédito dentro de una colección
            return view('credito.showCredito', ['creditos' => collect([$credito])]);
        }

        // Si es texto, se interpreta como nombre de usuario (solo admins)
        if ($user->hasRole('administrador')) {
            // Búsqueda por nombre de usuario con coincidencia exacta (ILIKE)
            $usuario = User::where('nombre_usuario', 'ILIKE', '%' . $busqueda . '%')->get();

            if (!$usuario) {
                return back()->with('error', 'Usuario no encontrado.');
            }

            // Buscamos todos los créditos del usuario encontrado
            $creditos = Credito::with('user')->whereIn('id_user', $usuario->pluck('id_user'))->get();

            if ($creditos->isEmpty()) {
                return back()->with('error', 'No se encontraron créditos para el usuario "' . $busqueda . '".');
            }

            return view('credito.showCredito', ['creditos' => $creditos]);
        }

        // Usuario no admin intenta buscar por nombre (no permitido)
        return back()->with('error', 'Solo puedes buscar tus créditos por ID.');
    }



    public function edit($id)
    {
        $credito = Credito::find($id);
        // $cliente = Cliente::all();
        // $compra = Compra::all();

        if (!$credito) {
            return redirect()->back()->with('error', 'El credito no se encontró.');
                // return redirect()->route('/producto/productoIndex')->with('error', 'El producto no se encontró.');
        }
        return view('/credito/editCredito', ['credito' => $credito]);
    }

    public function update(Request $request, Credito $credito)
    {
        $credito = Credito::find($credito->id_credito);

        if (!$credito) {
            return redirect()->route('credito.index')->with('error', 'El crédito no se encontró.');
        }

        $credito->fecha_liquidacion = $request->input('fecha_liquidacion', $credito->fecha_liquidacion);
        $credito->fecha_vencimiento = $request->input('fecha_vencimiento', $credito->fecha_vencimiento);
        $credito->estado = $request->input('estado', $credito->estado);

        $credito->save();

        return redirect()->route('credito.index')->with('success', 'El crédito se ha actualizado con éxito.');
    }

    public function destroy($id)
    {
        $credito = Credito::find($id);

        if (!$credito) {
            return redirect()->route('credito.index')->with('error', 'El crédito no se encontró.');
        }

        if ($credito->fecha_vencimiento && $credito->fecha_vencimiento < now()) {
            return redirect()->route('credito.index')->with('error', 'El crédito no se puede eliminar porque está vencido.');
        }

        if ($credito->saldo_total > 0) {
            return redirect()->route('credito.index')->with('error', 'El crédito no se puede eliminar porque tiene saldo pendiente.');
        }

        if ($credito->estado == 0) {
            return redirect()->route('credito.index')->with('error', 'El crédito no se puede eliminar porque está cerrado y tiene un historial.');
        }

        // Desvincular pedidos antes de eliminar el crédito
        Pedido::where('id_credito', $id)->update([
            'id_credito' => null,
            'metodo_pago' => 'contado',
        ]);

        // Eliminar abonos relacionados
        Abono::where('id_credito', $id)->delete();

        // Eliminar el crédito
        $credito->delete();

        return redirect()->route('credito.index')->with('success', 'El crédito se ha eliminado con éxito.');
    }

}
