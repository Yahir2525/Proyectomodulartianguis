<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/pedido/showPedido.css') }}">
    <title>Detalle(s) del Pedido</title>
</head>
<body>
<div class="page-container">
<main class="content">
<br><x-barrageneral/>
<section class="pedidos-container">
    <br><hr class="hr-grueso"><center><h1>Detalles del pedido</h1></center><hr class="hr-grueso"><br>

    @if(session('success'))
        <p style="color: green;"><strong>{{ session('success') }}</strong></p>
    @endif
    @if(session('error'))
        <p style="color: red;"><strong>{{ session('error') }}</strong></p>
    @endif

    <form action="{{ url('/pedido/showPedido') }}" method="GET" class="buscar">
        <label for="buscar">Buscar por ID de pedido o nombre de usuario:</label>
        <input 
            type="text" 
            id="buscar" 
            name="buscar" 
            placeholder="Ej. 21 o Pepito"
            list="{{ Auth::user()->hasRole('administrador') ? 'usuarios' : '' }}"
            value="{{ request('buscar') }}"
            autocomplete="off"
        />

        @if(Auth::user()->hasRole('administrador'))
            <datalist id="usuarios">
                @foreach($usuarios as $usuario)
                    <option value="{{ $usuario->nombre_usuario }}"></option>
                @endforeach
            </datalist>
        @endif

        <input type="submit" value="Buscar" />
    </form>


    @if(isset($pedidos) && $pedidos->isNotEmpty())

        @php
            $pedidosPorUsuario = $pedidos->groupBy('id_user');
        @endphp

        @foreach($pedidosPorUsuario as $idUser => $pedidosUsuario)
            <h2>Pedidos de: {{ optional($pedidosUsuario->first()->user)->nombre_usuario ?? 'Desconocido' }}</h2>

            @php
                $usuario = $pedidosUsuario->first()->user;

                // Activos (estado=1 y no vencidos por fecha)
                $creditosActivos = \App\Models\Credito::where('id_user', $idUser)
                    ->where('estado', 1)
                    ->whereDate('fecha_vencimiento', '>=', now())
                    ->get();
                    
                $creditosTodosActivos = \App\Models\Credito::where('id_user', $idUser)
                    ->where('estado', 1)
                    ->get();

                // Vencidos por fecha
                $creditosVencidos = $creditosTodosActivos->filter(fn($c) => $c->fecha_vencimiento < now());

                // Solo vencidos con saldo > 0
                $creditosVencidosConSaldo = $creditosVencidos->filter(fn($c) => (float)$c->saldo_total > 0);

                $puedeCrearCredito = $creditosTodosActivos->count() < 3;

                $nivelUsuarioGlobal = strtolower((string)($usuario->nivel ?? $usuario->nivel_usuario ?? $usuario->nivel_riesgo ?? ''));
            @endphp
            {{-- ===================== Pedidos con crédito ===================== --}}
            @php
                $pedidosConCredito = $pedidosUsuario->filter(fn($p) => !is_null($p->id_credito));
            @endphp

            @if($pedidosConCredito->isNotEmpty())
                <h3>Pedidos con crédito</h3>
                <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>ID Pedido</th>
                            <th>Crédito</th>
                            <th>Total</th>
                            <th>Creado</th>
                            <th>Actualizado</th>
                            <th>Eliminar</th>
                            <th>Método</th>
                            <th>Reabrir</th>
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
                                $bloqueadoPorHistorial = $creditosVencidosConSaldo->count() > 2;
                                $nivelUsuario = strtolower((string)($usuario->nivel ?? $usuario->nivel_usuario ?? $usuario->nivel_riesgo ?? ''));
                                $bloqueadoPorNivel = ($nivelUsuario === 'malo');
                                $bloqueoCreditoUI = $superaDiezMil || $bloqueadoPorHistorial || $bloqueadoPorNivel;
                            @endphp
                            <tr>
                                <td data-label="ID">{{ $pedido->id_pedido }}</td>
                                <td data-label="Crédito">{{ $pedido->id_credito }}</td>
                                <td data-label="Total">${{ number_format($totalPedido, 2) }}</td>
                                <td data-label="Creado">{{ $pedido->created_at }}</td>
                                <td data-label="Actualizado">{{ $pedido->updated_at }}</td>

                                <td data-label="Eliminar">
                                    @if($pedido->estado_pedido == 1)
                                        <form action="{{ url('/pedido', $pedido->id_pedido) }}" method="POST" onsubmit="return confirm('¿Seguro que quieres eliminar este pedido?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                        </form>
                                    @else
                                        <span class="badge bg-gray">Pedido cerrado</span>
                                    @endif
                                </td>

                                <td data-label="Método">
                                    @if($pedido->estado_pedido == 1)
                                        <form action="{{ route('pedido.cerrar', $pedido->id_pedido) }}" method="POST" class="form-cierre">
                                            @csrf
                                            <input type="hidden" name="total" value="{{ $totalPedido }}" />

                                            <label for="metodo_pago_{{ $pedido->id_pedido }}">Método de pago:</label>
                                            <select name="metodo_pago" class="form-select form-select-sm metodo-select"
                                                    required onchange="toggleCreditoOptions(this, {{ $pedido->id_pedido }})">
                                                <option value="" {{ $metodoPagoActual === '' ? 'selected' : '' }}>-- Selecciona --</option>
                                                <option value="contado" {{ $metodoPagoActual === 'contado' ? 'selected' : '' }}>Contado</option>
                                                @if(!$bloqueoCreditoUI)
                                                    <option value="credito" {{ $metodoPagoActual === 'credito' ? 'selected' : '' }}>Crédito</option>
                                                @endif
                                            </select>

                                            <div id="credito-opciones-{{ $pedido->id_pedido }}" style="margin-top:8px; {{ ($metodoPagoActual === 'credito' && !$bloqueoCreditoUI) ? 'display:block;' : 'display:none;' }}">
                                                <label>Seleccionar crédito:</label>
                                                <select name="id_credito" class="form-select form-select-sm metodo-select">
                                                    @if($puedeCrearCredito)
                                                        <option value="" {{ is_null($creditoSeleccionado) ? 'selected' : '' }}>-- Crear nuevo crédito --</option>
                                                    @endif
                                                    @foreach($creditosValidos as $credito)
                                                        <option value="{{ $credito->id_credito }}" {{ $creditoSeleccionado == $credito->id_credito ? 'selected' : '' }}>
                                                            Crédito #{{ $credito->id_credito }} - Saldo: ${{ number_format($credito->saldo_total, 2) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <button type="submit" class="btn btn-primary btn-sm w-100 mt-2">Cerrar pedido</button>
                                        </form>
                                    @else
                                        <span class="badge bg-gray">{{ ucfirst($pedido->metodo_pago ?? 'Sin seleccionar') }}</span>
                                    @endif
                                </td>

                                <td data-label="Reabrir">
                                    @if($pedido->estado_pedido == 0)
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
                                                    <button type="submit" class="btn btn-edit btn-sm">Reabrir</button>
                                                </form>
                                            @else
                                                <span class="badge bg-gray">Crédito cerrado</span>
                                            @endif
                                        @else
                                            <span class="badge bg-gray">Pedido cerrado</span>
                                        @endcan
                                    @else
                                        <span class="badge bg-gray">Pedido abierto</span>
                                    @endif
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            @endif

            @php
                $pedidosSinCredito = $pedidosUsuario->filter(fn($p) => is_null($p->id_credito));
            @endphp

            @if($pedidosSinCredito->isNotEmpty())
                <h3>Pedidos sin crédito</h3>
                <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>ID Pedido</th>
                            <th>Crédito</th>
                            <th>Total</th>
                            <th>Creado</th>
                            <th>Actualizado</th>
                            <th>Eliminar</th>
                            <th>Método</th>
                            <th>Reabrir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pedidosSinCredito as $pedido)
                            @php
                                $totalPedido = $pedido->total_pedido;
                                $metodoPagoActual = $pedido->metodo_pago ?? '';
                                $bloqueadoPorHistorial = $creditosVencidosConSaldo->count() > 2;
                                $nivelUsuario = strtolower((string)($usuario->nivel_usuario ?? $usuario->nivel ?? $usuario->nivel_riesgo ?? ''));
                                $bloqueadoPorNivel = ($nivelUsuario === 'malo');
                                $bloqueoCreditoUI = $bloqueadoPorHistorial || $bloqueadoPorNivel;
                            @endphp
                            <tr>
                                <td data-label="ID">{{ $pedido->id_pedido }}</td>
                                <td data-label="Crédito">N/A</td>
                                <td data-label="Total">${{ number_format($totalPedido, 2) }}</td>
                                <td data-label="Creado">{{ $pedido->created_at }}</td>
                                <td data-label="Actualizado">{{ $pedido->updated_at }}</td>

                                <td data-label="Eliminar">
                                    @if($pedido->estado_pedido == 1)
                                        <form action="{{ url('/pedido', $pedido->id_pedido) }}" method="POST" onsubmit="return confirm('¿Seguro que quieres eliminar este pedido?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                        </form>
                                    @else
                                        <span class="badge bg-gray">Pedido cerrado</span>
                                    @endif
                                </td>

                                <td data-label="Método">
                                    @if($pedido->estado_pedido == 1)
                                        <form action="{{ route('pedido.cerrar', $pedido->id_pedido) }}" method="POST" class="form-cierre">
                                            @csrf
                                            <input type="hidden" name="total" value="{{ $totalPedido }}" />
                                            <select name="metodo_pago" class="form-select form-select-sm metodo-select" required>
                                                <option value="" {{ $metodoPagoActual === '' ? 'selected' : '' }}>-- Selecciona --</option>
                                                <option value="contado" {{ $metodoPagoActual === 'contado' ? 'selected' : '' }}>Contado</option>
                                                @if(!$bloqueoCreditoUI)
                                                    <option value="credito" {{ $metodoPagoActual === 'credito' ? 'selected' : '' }}>Crédito</option>
                                                @endif
                                            </select>
                                            <button type="submit" class="btn btn-primary btn-sm w-100 mt-2">Cerrar pedido</button>
                                        </form>
                                    @else
                                        <span class="badge bg-gray">{{ ucfirst($pedido->metodo_pago ?? 'Sin seleccionar') }}</span>
                                    @endif
                                </td>

                                <td data-label="Reabrir">
                                    @if($pedido->estado_pedido == 0)
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
                                                    <button type="submit" class="btn btn-edit btn-sm">Reabrir</button>
                                                </form>
                                            @else
                                                <span class="badge bg-gray">Crédito cerrado</span>
                                            @endif
                                        @else
                                            <span class="badge bg-gray">Pedido cerrado</span>
                                        @endcan
                                    @else
                                        <span class="badge bg-gray">Pedido abierto</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
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
</section>
</main>
<x-footer/>
</div>
</body>
</html>
