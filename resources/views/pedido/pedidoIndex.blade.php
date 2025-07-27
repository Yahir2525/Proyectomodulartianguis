<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Principal de pedidos</title>
</head>
<body>
    <h1>Principal de pedidos</h1>

    @if(Auth::check())
        <p><a href="{{ url('/pedido/create') }}">Registrar un nuevo pedido</a></p>

        <form action="{{ url('/pedido/showPedido') }}" method="GET">
            <label for="id_pedido">Buscar por ID de pedido:</label>
            <input type="text" id="id_pedido" name="id_pedido" placeholder="Ej. 21" />
            @can('edit pedido')
            <label for="nombre_usuario">o por nombre de usuario:</label>
            <input type="text" id="nombre_usuario" name="nombre_usuario" placeholder="Ej. Pepito" />
            @endcan
            <input type="submit" value="Buscar" />
        </form>

        @if($pedidoIndex->isNotEmpty())
            @php
                $pedidosPorCredito = $pedidoIndex->groupBy('id_credito');

                // Función helper para validar bloqueo por usuario
                function usuarioBloqueado($userId) {
                    $creditos = \App\Models\Credito::where('id_user', $userId)->where('estado', 1)->get();
                    return ($creditos->count() >= 3 || $creditos->sum('saldo_total') > 10000);
                }
            @endphp

            @foreach($pedidosPorCredito as $idCredito => $pedidos)
                <h2>{{ $idCredito ? 'Pedidos del crédito #' . $idCredito : 'Pedidos no adquiridos a crédito' }}</h2>

                <table border="1" cellpadding="5" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID pedido</th>
                            <th>Usuario</th>
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
                                $bloqueado = usuarioBloqueado($pedido->id_user);
                            @endphp
                            <tr>
                                <td>{{ $pedido->id_pedido }}</td>
                                <td>{{ optional($pedido->user)->nombre_usuario ?? 'Sin cliente' }}</td>
                                <td>{{ $pedido->id_credito ?? 'N/A' }}</td>
                                <td>${{ number_format($pedido->total_pedido, 2) }}</td>
                                <td>{{ $pedido->estado_pedido == 1 ? 'Abierto' : 'Cerrado' }}</td>
                                <td>{{ $pedido->metodo_pago ?? 'Sin seleccionar' }}</td>
                                <td>{{ $pedido->created_at }}</td>
                                <td>{{ $pedido->updated_at }}</td>

                                {{-- Mostrar botones solo si pedido está abierto --}}
                                @if($pedido->estado_pedido == 1)
                                    <td>
                                        <a href="{{ route('pedido.edit', $pedido->id_pedido) }}?total={{ $pedido->total_pedido }}">Editar</a>
                                    </td>
                                    <td>
                                        <form action="{{ url('/pedido', $pedido->id_pedido) }}" method="POST">
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
                                        @php
                                            $usuario = $pedido->user;
                                            $creditosActivos = \App\Models\Credito::where('id_user', $usuario->id_user)->where('estado', 1)->get();
                                            $bloqueadoPorCreditos = $creditosActivos->count() >= 3;

                                            $saldoSimulado = null;
                                            foreach ($creditosActivos as $credito) {
                                                if ($credito->saldo_total + $pedido->total_pedido > 10000) {
                                                    $saldoSimulado = $credito->saldo_total + $pedido->total_pedido;
                                                    break;
                                                }
                                            }

                                            $superaSaldo = $saldoSimulado !== null;
                                            $bloqueado = $bloqueadoPorCreditos || $superaSaldo;
                                        @endphp

                                        <form action="{{ route('pedido.cerrar', $pedido->id_pedido) }}" method="POST" class="form-cierre">
                                            @csrf
                                            <input type="hidden" name="total" value="{{ $pedido->total_pedido }}" />

                                            @if($bloqueado)
                                                <p style="color:red; font-weight:bold; margin-top:8px;">
                                                    El usuario tiene restricciones para usar crédito:
                                                    <br>
                                                    @if($bloqueadoPorCreditos)
                                                        - Ya tiene 3 o más créditos activos.<br>
                                                    @endif
                                                    @if($superaSaldo)
                                                        - El saldo superaría los $10,000 con este pedido.<br>
                                                    @endif
                                                    Puedes cerrar este pedido como contado.
                                                </p>
                                            @endif

                                            <label for="metodo_pago_{{ $pedido->id_pedido }}">Método de pago:</label>
                                            <select name="metodo_pago" required @if($bloqueado) onchange="activarContadoSiValido(this)" @endif>
                                                <option value="">-- Selecciona --</option>
                                                <option value="contado">Contado</option>
                                                @if(!$bloqueado)
                                                    <option value="credito">Crédito</option>
                                                @endif
                                            </select>

                                            @php
                                                $creditos = \App\Models\Credito::where('id_user', $pedido->id_user)->get();
                                            @endphp

                                            <div class="credito-opciones" style="display:none; margin-top:8px;">
                                                @if($creditos->isNotEmpty())
                                                    <label>Seleccionar crédito:</label>
                                                    <select name="id_credito" class="select-credito">
                                                        <option value="">-- Crear nuevo crédito --</option>
                                                        @foreach($creditos as $credito)
                                                            <option value="{{ $credito->id_credito }}">
                                                                Crédito #{{ $credito->id_credito }} - Saldo: {{ $credito->saldo_total }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                @else
                                                    <input type="hidden" name="id_credito" value="">
                                                @endif
                                            </div>

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
            @endforeach
        @endif
    @endif

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.form-cierre').forEach(form => {
        const metodo = form.querySelector('[name="metodo_pago"]');
        const creditoOpciones = form.querySelector('.credito-opciones');

        if (metodo && creditoOpciones) {
            metodo.addEventListener('change', () => {
                if (metodo.value === 'credito') {
                    creditoOpciones.style.display = 'block';
                } else {
                    creditoOpciones.style.display = 'none';
                }
            });

            // Ejecutar al inicio por si hay valores persistidos
            metodo.dispatchEvent(new Event('change'));
        }
    });
});

function activarContadoSiValido(select) {
    if (select.value === 'credito') {
        alert('No puedes usar crédito. El usuario tiene 3 créditos activos o el saldo superaría los $10,000. Elige contado.');
        select.value = '';
    }
}
</script>


</body>
</html>
