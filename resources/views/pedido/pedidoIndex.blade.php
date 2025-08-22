<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <link rel="stylesheet" href="{{ asset('css/pedido/pedidoIndex.css') }}">
    <title>Principal de pedidos</title>
</head>
<body>
<h1>Principal de pedidos</h1>

@if(Auth::check())
    <p><a href="{{ url('/pedido/create') }}">Registrar un nuevo pedido</a></p>

    <form action="{{ url('/pedido/showPedido') }}" method="GET">
        <label for="busqueda">Buscar por ID de pedido o nombre de usuario:</label>
        <input type="text" id="busqueda" name="busqueda" placeholder="Ej. 21 o Pepito"
            list="{{ Auth::user()->can('edit pedido') ? 'usuarios' : '' }}"
            value="{{ request('busqueda') }}" />

        @can('edit pedido')
            <datalist id="usuarios">
                @foreach($usuarios as $usuario)
                    <option value="{{ $usuario->nombre_usuario }}"></option>
                @endforeach
            </datalist>
        @endcan

        <input type="submit" value="Buscar" />
    </form>

    @if(session('success'))
        <p style="color: green; font-weight: bold;">{{ session('success') }}</p>
    @endif
    @if(session('error'))
        <p style="color: red; font-weight: bold;">{{ session('error') }}</p>
    @endif

    @if($pedidoIndex->isNotEmpty())
        @php
            // Agrupamos los pedidos por usuario
            $pedidosPorUsuario = $pedidoIndex->groupBy('id_user');
        @endphp

        @foreach($pedidosPorUsuario as $idUser => $pedidosUsuario)
            <h2>Pedidos de: {{ optional($pedidosUsuario->first()->user)->nombre_usuario ?? 'Usuario desconocido' }}</h2>

            @php
                $pedidosPorCredito = $pedidosUsuario->groupBy('id_credito');

                // Suponiendo que tienes método en User para pagos vencidos
                $usuario = $pedidosUsuario->first()->user;
                $usuarioBloqueadoPorPagosAtrasados = $usuario && method_exists($usuario, 'tienePagosAtrasadosSinAbonar')
                    ? $usuario->tienePagosAtrasadosSinAbonar()
                    : false;

                /* NUEVO: detectar nivel del usuario (normalizado, con fallbacks) */
                $nivelUsuarioGlobal = $usuario
                    ? strtolower((string)($usuario->nivel ?? $usuario->nivel_usuario ?? $usuario->nivel_riesgo ?? ''))
                    : '';
                $bloqueadoPorNivelGlobal = ($nivelUsuarioGlobal === 'malo');
            @endphp

            @if($usuarioBloqueadoPorPagosAtrasados)
                <p style="color: red; font-weight: bold;">
                    El usuario tiene pagos vencidos sin abonar. No podrá cerrar pedidos a crédito hasta liquidarlos.
                </p>
            @endif

            <!-- NUEVO: aviso global por nivel malo -->
            @if($bloqueadoPorNivelGlobal)
                <p style="color: red; font-weight: bold;">
                    El nivel del usuario es <strong>"malo"</strong>. Solo puede cerrar pedidos <strong>a contado</strong>.
                </p>
            @endif

            @foreach($pedidosPorCredito as $idCredito => $pedidos)
                <h3>{{ $idCredito ? 'Pedidos del crédito #' . $idCredito : 'Pedidos no adquiridos a crédito' }}</h3>

                <table border="1" cellpadding="5" cellspacing="0">
                    <thead>
                    <tr>
                        <th>ID pedido</th>
                        <th>Crédito</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Método</th>
                        <th>Creado</th>
                        <th>Actualizado</th>
                        <th>Editar</th>
                        <th>Eliminar</th>
                        <th>Acción</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($pedidos as $pedido)
                        @php
                            $usuario = $pedido->user;
                            $totalPedido = $pedido->total_pedido;

                            $creditosActivos = \App\Models\Credito::where('id_user', $usuario->id_user)
                                ->where('estado', 1)
                                ->whereDate('fecha_vencimiento', '>=', now())
                                ->get();

                            $deudaActual = $creditosActivos->sum('saldo_total');
                            $superaDiezMil = $deudaActual >= 10000 || ($deudaActual + $totalPedido) > 10000;

                            $creditosValidos = $creditosActivos->filter(function($c) use ($totalPedido) {
                                return ($c->saldo_total + $totalPedido) <= 10000;
                            });

                            $creditosVencidos = \App\Models\Credito::where('id_user', $usuario->id_user)
                                ->where('estado', 1)
                                ->whereDate('fecha_vencimiento', '<', now())
                                ->get();

                            $puedeCrearNuevoCredito = $creditosActivos->count() < 3;

                            // Si el usuario tiene pagos vencidos sin abonar (usar método en User)
                            $usuarioBloqueadoPorPagosAtrasados = $usuario && method_exists($usuario, 'tienePagosAtrasadosSinAbonar')
                                ? $usuario->tienePagosAtrasadosSinAbonar()
                                : false;

                            $nivelUsuario = strtolower((string)($usuario->nivel_usuario ?? ''));
                            $bloqueadoPorNivel = ($nivelUsuario === 'malo');

                            $bloqueoCreditoUI = $superaDiezMil || $usuarioBloqueadoPorPagosAtrasados || $bloqueadoPorNivel;
                        @endphp

                        <tr>
                            <td>{{ $pedido->id_pedido }}</td>
                            <td>{{ $pedido->id_credito ?? 'N/A' }}</td>
                            <td>${{ number_format($totalPedido, 2) }}</td>
                            <td>{{ $pedido->estado_pedido == 1 ? 'Abierto' : 'Cerrado' }}</td>
                            <td>{{ $pedido->metodo_pago ?? 'Sin seleccionar' }}</td>
                            <td>{{ $pedido->created_at }}</td>
                            <td>{{ $pedido->updated_at }}</td>

                            @if($pedido->estado_pedido == 1)
                                <td><a href="{{ route('pedido.edit', $pedido->id_pedido) }}?total={{ $totalPedido }}">Editar</a></td>
                                <td>
                                    <form action="{{ url('/pedido', $pedido->id_pedido) }}" method="POST" onsubmit="return confirm('¿El cliente regresó todos los productos y/o canceló el pedido correctamente?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit">Eliminar</button>
                                    </form>
                                </td>
                            @else
                                <td></td>
                                <td></td>
                            @endif

                            <td>
                                @if($pedido->estado_pedido == 1)
                                    <form action="{{ route('pedido.cerrar', $pedido->id_pedido) }}" method="POST" class="form-cierre">
                                        @csrf
                                        <input type="hidden" name="total" value="{{ $totalPedido }}" />

                                        @if($superaDiezMil)
                                            <p style="color:red;">
                                                <strong>No puedes cerrar este pedido con crédito:</strong><br>
                                                @if($deudaActual >= 10000)
                                                    - El usuario ya debe ${{ number_format($deudaActual, 2) }}.<br>
                                                @endif
                                                @if(($deudaActual + $totalPedido) > 10000)
                                                    - Con este pedido, la deuda sería ${{ number_format($deudaActual + $totalPedido, 2) }}.<br>
                                                @endif
                                                Usa la opción <strong>Contado</strong>.
                                            </p>
                                        @endif

                                        @if($usuarioBloqueadoPorPagosAtrasados)
                                            <p style="color:red;">
                                                <strong>Atención:</strong> El usuario tiene pagos vencidos sin abonar.<br>
                                                No podrá cerrar pedidos a crédito hasta liquidarlos.
                                            </p>
                                        @endif

                                        @if($bloqueadoPorNivel)
                                            <p style="color:red;">
                                                <strong>Atención:</strong> El nivel del usuario es <strong>"malo"</strong>.<br>
                                                Solo puede cerrar pedidos <strong>a contado</strong>.
                                            </p>
                                        @endif

                                        <label for="metodo_pago_{{ $pedido->id_pedido }}">Método de pago:</label>
                                        <select name="metodo_pago" required onchange="toggleCreditoOptions(this, {{ $pedido->id_pedido }})">
                                            <option value="">-- Selecciona --</option>
                                            <option value="contado" {{ $pedido->metodo_pago == 'contado' ? 'selected' : '' }}>Contado</option>
                                            @if(!$bloqueoCreditoUI)
                                                <option value="credito" {{ $pedido->metodo_pago == 'credito' ? 'selected' : '' }}>Crédito</option>
                                            @endif
                                        </select>

                                        <div id="credito-opciones-{{ $pedido->id_pedido }}" style="display:{{ ($pedido->metodo_pago == 'credito' && !$bloqueoCreditoUI) ? 'block' : 'none' }}; margin-top:8px;">
                                            <label>Seleccionar crédito:</label>
                                            <select name="id_credito" class="select-credito">
                                                @if($puedeCrearNuevoCredito)
                                                    <option value="" {{ $pedido->id_credito === null ? 'selected' : '' }}>-- Crear nuevo crédito --</option>
                                                @endif
                                                @foreach($creditosValidos as $credito)
                                                    <option value="{{ $credito->id_credito }}" {{ $pedido->id_credito == $credito->id_credito ? 'selected' : '' }}>
                                                        Crédito #{{ $credito->id_credito }} - Saldo: ${{ number_format($credito->saldo_total, 2) }}
                                                    </option>
                                                @endforeach
                                            </select>

                                            @if(!$puedeCrearNuevoCredito)
                                                <p style="color:orange; font-style: italic;">
                                                    Ya tienes 3 créditos activos. No puedes crear uno nuevo, pero puedes usar los existentes.
                                                </p>
                                            @endif
                                        </div>

                                        <button type="submit" style="margin-top:8px;">Cerrar pedido</button>
                                    </form>
                                @else
                                    @can('edit pedido')
                                        @php
                                            $puedeReabrir = true;
                                            if ($pedido->id_credito) {
                                                $credito = \App\Models\Credito::find($pedido->id_credito);
                                                if (!$credito || $credito->estado == 0 || now()->greaterThan($credito->fecha_vencimiento)) {
                                                    $puedeReabrir = false;
                                                }
                                            }
                                        @endphp

                                        @if ($puedeReabrir)
                                            <form action="{{ route('pedido.reabrir', $pedido->id_pedido) }}" method="POST">
                                                @csrf
                                                <button type="submit">Reabrir pedido</button>
                                            </form>
                                        @else
                                            <p style="color: red;">No se puede reabrir: el crédito está cerrado o vencido.</p>
                                        @endif
                                    @else
                                        Pedido cerrado
                                    @endcan
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endforeach
            <hr style="margin: 40px 0;">
        @endforeach

    @else
        <p>No hay pedidos registrados.</p>
    @endif
@endif

<script>
function toggleCreditoOptions(select, idPedido) {
    const div = document.getElementById('credito-opciones-' + idPedido);
    div.style.display = select.value === 'credito' ? 'block' : 'none';
}
</script>
</body>
</html>
