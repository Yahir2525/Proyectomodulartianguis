<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="{{ asset('css/pedido/showPedido.css') }}">
    <title>Detalle(s) del Pedido</title>
</head>
<body>
    <h1>Detalle(s) del Pedido</h1>

    @if(session('success'))
        <p style="color: green;"><strong>{{ session('success') }}</strong></p>
    @endif
    @if(session('error'))
        <p style="color: red;"><strong>{{ session('error') }}</strong></p>
    @endif

    @if(isset($pedidos) && $pedidos->isNotEmpty())

        @php
            $pedidosPorUsuario = $pedidos->groupBy('id_user');
        @endphp

        @foreach($pedidosPorUsuario as $idUser => $pedidosUsuario)
            <h2>Pedidos de: {{ optional($pedidosUsuario->first()->user)->nombre_usuario ?? 'Desconocido' }}</h2>

            @php
                // Obtener créditos activos y todos los créditos del usuario
                $usuario = $pedidosUsuario->first()->user;

                $creditosActivos = \App\Models\Credito::where('id_user', $idUser)
                    ->where('estado', 1)
                    ->whereDate('fecha_vencimiento', '>=', now())
                    ->get();

                $creditosTodosActivos = \App\Models\Credito::where('id_user', $idUser)
                    ->where('estado', 1)
                    ->get();

                $creditosVencidos = $creditosTodosActivos->filter(fn($c) => $c->fecha_vencimiento < now());

                $puedeCrearCredito = $creditosTodosActivos->count() < 3;
            @endphp

            {{-- Pedidos con crédito --}}
            @php
                $pedidosConCredito = $pedidosUsuario->filter(fn($p) => !is_null($p->id_credito));
            @endphp

            @if($pedidosConCredito->isNotEmpty())
                <h3>Pedidos con crédito</h3>
                <table border="1" cellpadding="5" cellspacing="0" style="margin-bottom:20px;">
                    <thead>
                        <tr>
                            <th>ID Pedido</th>
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
                        @foreach($pedidosConCredito as $pedido)
                            @php
                                $totalPedido = $pedido->total_pedido;

                                $deudaActual = $creditosActivos->sum('saldo_total');
                                $superaDiezMil = $deudaActual >= 10000 || ($deudaActual + $totalPedido) > 10000;

                                $creditosValidos = $creditosActivos->filter(fn($c) => ($c->saldo_total + $totalPedido) <= 10000);

                                $metodoPagoActual = $pedido->metodo_pago ?? '';
                                $creditoSeleccionado = $pedido->id_credito;
                            @endphp
                            <tr>
                                <td>{{ $pedido->id_pedido }}</td>
                                <td>{{ $pedido->id_credito }}</td>
                                <td>${{ number_format($totalPedido, 2) }}</td>
                                <td>{{ $pedido->estado_pedido == 1 ? 'Abierto' : 'Cerrado' }}</td>
                                <td>{{ $pedido->metodo_pago ?? 'Sin seleccionar' }}</td>
                                <td>{{ $pedido->created_at }}</td>
                                <td>{{ $pedido->updated_at }}</td>

                                @if($pedido->estado_pedido == 1)
                                    <td><a href="{{ route('pedido.edit', $pedido->id_pedido) }}?total={{ $totalPedido }}">Editar</a></td>
                                    <td>
                                        <form action="{{ url('/pedido', $pedido->id_pedido) }}" method="POST" onsubmit="return confirm('¿Seguro que quieres eliminar este pedido?');">
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

                                            <label for="metodo_pago_{{ $pedido->id_pedido }}">Método de pago:</label>
                                            <select name="metodo_pago" required onchange="toggleCreditoOptions(this, {{ $pedido->id_pedido }})">
                                                <option value="" {{ $metodoPagoActual === '' ? 'selected' : '' }}>-- Selecciona --</option>
                                                <option value="contado" {{ $metodoPagoActual === 'contado' ? 'selected' : '' }}>Contado</option>
                                                @if(!$superaDiezMil)
                                                    <option value="credito" {{ $metodoPagoActual === 'credito' ? 'selected' : '' }}>Crédito</option>
                                                @endif
                                            </select>

                                            <div id="credito-opciones-{{ $pedido->id_pedido }}" style="margin-top:8px; {{ $metodoPagoActual === 'credito' ? 'display:block;' : 'display:none;' }}">
                                                <label>Seleccionar crédito:</label>
                                                <select name="id_credito" class="select-credito">
                                                    @if($puedeCrearCredito)
                                                        <option value="" {{ is_null($creditoSeleccionado) ? 'selected' : '' }}>-- Crear nuevo crédito --</option>
                                                    @endif
                                                    @foreach($creditosValidos as $credito)
                                                        <option value="{{ $credito->id_credito }}" {{ $creditoSeleccionado == $credito->id_credito ? 'selected' : '' }}>
                                                            Crédito #{{ $credito->id_credito }} - Saldo: ${{ number_format($credito->saldo_total, 2) }}
                                                        </option>
                                                    @endforeach
                                                </select>

                                                @if(!$puedeCrearCredito)
                                                    <p style="color:orange; font-style: italic;">
                                                        Ya tienes 3 créditos activos o créditos vencidos. No puedes crear uno nuevo, pero puedes usar los existentes.
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
                                                <p style="color:red;">No se puede reabrir: el crédito está cerrado o vencido.</p>
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
            @endif

            {{-- Pedidos sin crédito --}}
            @php
                $pedidosSinCredito = $pedidosUsuario->filter(fn($p) => is_null($p->id_credito));
            @endphp

            @if($pedidosSinCredito->isNotEmpty())
                <h3>Pedidos sin crédito</h3>
                <table border="1" cellpadding="5" cellspacing="0" style="margin-bottom:30px;">
                    <thead>
                        <tr>
                            <th>ID Pedido</th>
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
                        @foreach($pedidosSinCredito as $pedido)
                            @php
                                $totalPedido = $pedido->total_pedido;
                                $metodoPagoActual = $pedido->metodo_pago ?? '';
                            @endphp
                            <tr>
                                <td>{{ $pedido->id_pedido }}</td>
                                <td>${{ number_format($totalPedido, 2) }}</td>
                                <td>{{ $pedido->estado_pedido == 1 ? 'Abierto' : 'Cerrado' }}</td>
                                <td>{{ $pedido->metodo_pago ?? 'Sin seleccionar' }}</td>
                                <td>{{ $pedido->created_at }}</td>
                                <td>{{ $pedido->updated_at }}</td>

                                @if($pedido->estado_pedido == 1)
                                    <td><a href="{{ route('pedido.edit', $pedido->id_pedido) }}?total={{ $totalPedido }}">Editar</a></td>
                                    <td>
                                        <form action="{{ url('/pedido', $pedido->id_pedido) }}" method="POST" onsubmit="return confirm('¿Seguro que quieres eliminar este pedido?');">
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

                                            <label for="metodo_pago_{{ $pedido->id_pedido }}">Método de pago:</label>
                                            <select name="metodo_pago" required>
                                                <option value="" {{ $metodoPagoActual === '' ? 'selected' : '' }}>-- Selecciona --</option>
                                                <option value="contado" {{ $metodoPagoActual === 'contado' ? 'selected' : '' }}>Contado</option>
                                                <option value="credito" {{ $metodoPagoActual === 'credito' ? 'selected' : '' }}>Crédito</option>
                                            </select>

                                            <button type="submit" style="margin-top:8px;">Cerrar pedido</button>
                                        </form>
                                    @else
                                        @can('edit pedido')
                                            <form action="{{ route('pedido.reabrir', $pedido->id_pedido) }}" method="POST">
                                                @csrf
                                                <button type="submit">Reabrir pedido</button>
                                            </form>
                                        @else
                                            Pedido cerrado
                                        @endcan
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            <hr style="margin: 40px 0;">
        @endforeach

    @else
        <p style="color:red;">No se encontraron pedidos.</p>
    @endif

    <a href="{{ route('pedido.index') }}">← Volver a la lista de pedidos</a>

<script>
function toggleCreditoOptions(select, idPedido) {
    const div = document.getElementById('credito-opciones-' + idPedido);
    if (!div) return;
    div.style.display = select.value === 'credito' ? 'block' : 'none';
}
</script>
</body>
</html>
