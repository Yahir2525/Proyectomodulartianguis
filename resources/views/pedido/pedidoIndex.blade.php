<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/pedido/pedidoIndex.css') }}">
    <title>Principal de pedidos</title>
</head>
<body>
<div class="page-container">
<main class="content">
<br><x-barrageneral/>
<section class="pedidos-container">
<br><hr class="hr-grueso"><h1>Listado de pedidos</h1><hr class="hr-grueso"><br>

@if(Auth::check())
    <p>
        <a href="{{ url('/pedido/create') }}" class="btn btn-primary">Registrar un nuevo pedido</a>
    </p>

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


    @if(session('success'))
        <p class="info-msg" style="color: green;">{{ session('success') }}</p>
    @endif
    @if(session('error'))
        <p class="info-msg text-danger">{{ session('error') }}</p>
    @endif

    @if($pedidoIndex->isNotEmpty())
        @php
            $pedidosPorUsuario = $pedidoIndex->groupBy('id_user');
        @endphp

        @foreach($pedidosPorUsuario as $idUser => $pedidosUsuario)
            <h2>Pedidos de: {{ optional($pedidosUsuario->first()->user)->nombre_usuario ?? 'Usuario desconocido' }}</h2>

            @php
                $pedidosPorCredito = $pedidosUsuario->groupBy('id_credito');
                $usuario = $pedidosUsuario->first()->user;
                $creditosVencidos = \App\Models\Credito::where('id_user', $usuario->id_user)
                    ->where('estado', 1)
                    ->whereDate('fecha_vencimiento', '<', now())
                    ->get();

                $usuarioBloqueadoPorPagosAtrasados = $creditosVencidos->count() > 2;
                $nivelUsuarioGlobal = $usuario
                    ? strtolower((string)($usuario->nivel ?? $usuario->nivel_usuario ?? $usuario->nivel_riesgo ?? ''))
                    : '';
                $bloqueadoPorNivelGlobal = ($nivelUsuarioGlobal === 'malo');
            @endphp

            @if($usuarioBloqueadoPorPagosAtrasados)
                <p class="info-msg text-danger">
                    El usuario tiene pagos vencidos sin abonar. No podrá cerrar pedidos a crédito hasta liquidarlos.
                </p>
            @endif

            @if($bloqueadoPorNivelGlobal)
                <p class="info-msg text-danger">
                    El nivel del usuario es <strong>"malo"</strong>. Solo puede cerrar pedidos <strong>a contado</strong>.
                </p>
            @endif

            @foreach($pedidosPorCredito as $idCredito => $pedidos)
                <div class="table-wrap">
                    <h3 class="titulo-tabla">
                        {{ $idCredito ? 'Pedidos del crédito #' . $idCredito : 'Pedidos no adquiridos a crédito' }}
                    </h3>

                    @php
                        $hayCerrados = $pedidos->contains(fn($p) => $p->estado_pedido == 0);
                    @endphp

                    <table>
                        <thead>
                        <tr>
                            <th>ID pedido</th>
                            <th>Crédito</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Creado</th>
                            <th>Actualizado</th>
                            <th>Eliminar</th>
                            <th>Método</th>
                            @if($hayCerrados && Auth::user()->can('edit pedido'))
                                <th>Reabrir</th>
                            @endif
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
                                $puedeCrearNuevoCredito = $creditosActivos->count() < 3;
                                $nivelUsuario = strtolower((string)($usuario->nivel_usuario ?? ''));
                                $bloqueadoPorNivel = ($nivelUsuario === 'malo');
                                $bloqueoCreditoUI = $superaDiezMil || $bloqueadoPorNivel;
                            @endphp

                            <tr>
                                <td data-label="ID">{{ $pedido->id_pedido }}</td>
                                <td data-label="Crédito">{{ $pedido->id_credito ?? 'N/A' }}</td>
                                <td data-label="Total">${{ number_format($totalPedido, 2) }}</td>
                                <td data-label="Estado">{{ $pedido->estado_pedido == 1 ? 'Abierto' : 'Cerrado' }}</td>
                                <td data-label="Creado">{{ $pedido->created_at }}</td>
                                <td data-label="Actualizado">{{ $pedido->updated_at }}</td>
                                <td data-label="Eliminar">
                                    @if($pedido->estado_pedido == 1)
                                        <form action="{{ url('/pedido', $pedido->id_pedido) }}" method="POST" onsubmit="return confirm('¿Eliminar pedido?');">
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
                                        <div class="col-metodo">
                                            <form action="{{ route('pedido.cerrar', $pedido->id_pedido) }}" method="POST" class="form-cierre">
                                                @csrf
                                                <input type="hidden" name="total" value="{{ $totalPedido }}" />
                                                
                                                <select name="metodo_pago" class="form-select form-select-sm metodo-select"
                                                        required onchange="toggleCreditoOptions(this, {{ $pedido->id_pedido }})">
                                                    <option value="">-- Selecciona --</option>
                                                    <option value="contado" {{ $pedido->metodo_pago == 'contado' ? 'selected' : '' }}>Contado</option>
                                                    @if(!$bloqueoCreditoUI)
                                                        <option value="credito" {{ $pedido->metodo_pago == 'credito' ? 'selected' : '' }}>Crédito</option>
                                                    @endif
                                                </select>
                                                
                                                <div id="credito-opciones-{{ $pedido->id_pedido }}" class="credito-select"
                                                    style="display:{{ ($pedido->metodo_pago == 'credito' && !$bloqueoCreditoUI) ? 'block' : 'none' }};">
                                                    <select name="id_credito" class="form-select form-select-sm metodo-select">
                                                        @if($puedeCrearNuevoCredito)
                                                            <option value="" {{ $pedido->id_credito === null ? 'selected' : '' }}>-- Nuevo crédito --</option>
                                                        @endif
                                                        @foreach($creditosValidos as $credito)
                                                            <option value="{{ $credito->id_credito }}" {{ $pedido->id_credito == $credito->id_credito ? 'selected' : '' }}>
                                                                Crédito #{{ $credito->id_credito }} - Saldo: ${{ number_format($credito->saldo_total, 2) }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <button type="submit" class="btn btn-primary btn-sm w-100 mt-2">Cerrar pedido</button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="badge bg-secondary metodo-badge">
                                            {{ ucfirst($pedido->metodo_pago ?? 'Sin seleccionar') }}
                                        </span>
                                    @endif
                                </td>

                                @if($hayCerrados && Auth::user()->can('edit pedido'))
                                    <td data-label="Reabrir">
                                        @if($pedido->estado_pedido == 0)
                                            <form action="{{ route('pedido.reabrir', $pedido->id_pedido) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-edit btn-sm">Reabrir</button>
                                            </form>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
            <hr class="hr-grueso">
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
</section>
</main>
<x-footer/>
</div>
</body>
</html>
