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
<br>
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

                // Vencidos por fecha (subset en memoria para no repetir queries)
                $creditosVencidos = $creditosTodosActivos->filter(fn($c) => $c->fecha_vencimiento < now());

                // NUEVO: sólo nos interesan los vencidos con saldo > 0 para bloquear por “historial”
                $creditosVencidosConSaldo = $creditosVencidos->filter(fn($c) => (float)$c->saldo_total > 0);

                // Permitir crear crédito mientras no superes 3 activos (regla existente)
                $puedeCrearCredito = $creditosTodosActivos->count() < 3;

                /* ===================== BLOQUEOS GLOBALES ===================== */
                // Nivel
                $nivelUsuarioGlobal = strtolower((string)($usuario->nivel ?? $usuario->nivel_usuario ?? $usuario->nivel_riesgo ?? ''));
                $bloqueadoPorNivelGlobal = ($nivelUsuarioGlobal === 'malo');

                // HISTORIAL: AHORA SOLO BLOQUEA CON > 2 vencidos con saldo > 0 (permitimos 0, 1 o 2)
                $bloqueadoPorHistorialGlobal = $creditosVencidosConSaldo->count() > 2;
            @endphp

            @if($bloqueadoPorNivelGlobal)
                <p style="color:red;"><strong>Atención:</strong> El nivel del usuario es <strong>"malo"</strong>. Solo puede cerrar pedidos <strong>a contado</strong>.</p>
            @endif
            @if($bloqueadoPorHistorialGlobal)
                <p style="color:red;"><strong>Atención:</strong> El usuario tiene más de 2 créditos vencidos con saldo pendiente. No podrá cerrar pedidos a crédito.</p>
            @endif

            {{-- ===================== Pedidos con crédito ===================== --}}
            @php
                $pedidosConCredito = $pedidosUsuario->filter(fn($p) => !is_null($p->id_credito));
            @endphp

            @if($pedidosConCredito->isNotEmpty())
                <h3>Pedidos con crédito</h3>
                <div class="table-responsive">
                <table class="table table-bordered">
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

                                // Deuda actual considerando SOLO los activos no vencidos (como antes)
                                $deudaActual = $creditosActivos->sum('saldo_total');
                                $superaDiezMil = $deudaActual >= 10000 || ($deudaActual + $totalPedido) > 10000;

                                // Válidos (no rebasan 10k con el nuevo total)
                                $creditosValidos = $creditosActivos->filter(fn($c) => ($c->saldo_total + $totalPedido) <= 10000);

                                $metodoPagoActual = $pedido->metodo_pago ?? '';
                                $creditoSeleccionado = $pedido->id_credito;

                                // BLOQUEOS POR FILA (usamos las colecciones ya calculadas)
                                $bloqueadoPorHistorial = $creditosVencidosConSaldo->count() > 2; // NUEVO criterio
                                $nivelUsuario = strtolower((string)($usuario->nivel ?? $usuario->nivel_usuario ?? $usuario->nivel_riesgo ?? ''));
                                $bloqueadoPorNivel = ($nivelUsuario === 'malo');

                                // La UI bloquea crédito solo si excede 10k, o nivel malo, o >2 vencidos con saldo
                                $bloqueoCreditoUI = $superaDiezMil || $bloqueadoPorHistorial || $bloqueadoPorNivel;
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

                                            @if($bloqueadoPorHistorial)
                                                <p style="color:red;">
                                                    <strong>Atención:</strong> El usuario tiene más de 2 créditos vencidos con saldo pendiente. No puede cerrar a crédito.
                                                </p>
                                            @endif
                                            @if($bloqueadoPorNivel)
                                                <p style="color:red;">
                                                    <strong>Atención:</strong> El nivel del usuario es <strong>"malo"</strong>. Solo puede cerrar <strong>a contado</strong>.
                                                </p>
                                            @endif

                                            <label for="metodo_pago_{{ $pedido->id_pedido }}">Método de pago:</label>
                                            <select name="metodo_pago" required onchange="toggleCreditoOptions(this, {{ $pedido->id_pedido }})">
                                                <option value="" {{ $metodoPagoActual === '' ? 'selected' : '' }}>-- Selecciona --</option>
                                                <option value="contado" {{ $metodoPagoActual === 'contado' ? 'selected' : '' }}>Contado</option>
                                                @if(!$bloqueoCreditoUI)
                                                    <option value="credito" {{ $metodoPagoActual === 'credito' ? 'selected' : '' }}>Crédito</option>
                                                @endif
                                            </select>

                                            <div id="credito-opciones-{{ $pedido->id_pedido }}" style="margin-top:8px; {{ ($metodoPagoActual === 'credito' && !$bloqueoCreditoUI) ? 'display:block;' : 'display:none;' }}">
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
                </div>
            @endif

            @php
                $pedidosSinCredito = $pedidosUsuario->filter(fn($p) => is_null($p->id_credito));
            @endphp

            @if($pedidosSinCredito->isNotEmpty())
                <h3>Pedidos sin crédito</h3>
                <div class="table-responsive">
                <table class="table table-bordered">
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

                                // Mismos bloqueos que arriba pero sin deuda (>10k lo validará el controlador al crear)
                                $bloqueadoPorHistorial = $creditosVencidosConSaldo->count() > 2; // NUEVO
                                $nivelUsuario = strtolower((string)($usuario->nivel_usuario ?? $usuario->nivel ?? $usuario->nivel_riesgo ?? ''));
                                $bloqueadoPorNivel = ($nivelUsuario === 'malo');

                                $bloqueoCreditoUI = $bloqueadoPorHistorial || $bloqueadoPorNivel;
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

                                            @if($bloqueadoPorHistorial)
                                                <p style="color:red;">
                                                    <strong>Atención:</strong> El usuario tiene más de 2 créditos vencidos con saldo pendiente. No puede cerrar a crédito.
                                                </p>
                                            @endif
                                            @if($bloqueadoPorNivel)
                                                <p style="color:red;">
                                                    <strong>Atención:</strong> El nivel del usuario es <strong>"malo"</strong>. Solo puede cerrar <strong>a contado</strong>.
                                                </p>
                                            @endif>

                                            <label for="metodo_pago_{{ $pedido->id_pedido }}">Método de pago:</label>
                                            <select name="metodo_pago" required>
                                                <option value="" {{ $metodoPagoActual === '' ? 'selected' : '' }}>-- Selecciona --</option>
                                                <option value="contado" {{ $metodoPagoActual === 'contado' ? 'selected' : '' }}>Contado</option>
                                                @if(!$bloqueoCreditoUI)
                                                    <option value="credito" {{ $metodoPagoActual === 'credito' ? 'selected' : '' }}>Crédito</option>
                                                @endif
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
</main>
<x-footer/>
</div>
</body>
</html>
