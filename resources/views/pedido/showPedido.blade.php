<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle(s) del Pedido</title>
</head>
<body>
    <h1>Detalle(s) del Pedido</h1>

    @if (isset($pedidos) && $pedidos->isNotEmpty())
        <table border="1" cellpadding="5" cellspacing="0">
            <thead>
                <tr>
                    <th>ID pedido</th>
                    <th>Usuario</th>
                    <th>Método de pago</th>
                    <th>Estado</th>
                    <th>ID crédito</th>
                    <th>Total</th>
                    <th>Creado</th>
                    <th>Actualizado</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pedidos as $pedido)
                    <tr>
                        <td>{{ $pedido->id_pedido }}</td>
                        <td>{{ optional($pedido->user)->nombre_usuario ?? 'Sin usuario' }}</td>
                        <td>{{ $pedido->metodo_pago ?? 'Sin seleccionar' }}</td>
                        <td>{{ $pedido->estado_pedido == 1 ? 'Abierto' : 'Cerrado' }}</td>
                        <td>{{ $pedido->id_credito ?? 'N/A' }}</td>
                        <td>${{ number_format($pedido->total_pedido, 2) }}</td>
                        <td>{{ $pedido->created_at }}</td>
                        <td>{{ $pedido->updated_at }}</td>
                    </tr>
                    <tr>
                        <td colspan="8">
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
                                        <p style="color:red;">
                                            <strong>No puedes cerrar este pedido con crédito:</strong><br>
                                            @if($bloqueadoPorCreditos)
                                                - Tiene 3 o más créditos activos.<br>
                                            @endif
                                            @if($superaSaldo)
                                                - El saldo del crédito superaría $10,000.<br>
                                            @endif
                                            Puedes usar la opción **Contado**.
                                        </p>
                                    @endif

                                    <label>Método de pago:</label>
                                    <select name="metodo_pago" required @if($bloqueado) onchange="activarContadoSiValido(this)" @endif>
                                        <option value="">-- Selecciona --</option>
                                        <option value="contado">Contado</option>
                                        @if(!$bloqueado)
                                            <option value="credito">Crédito</option>
                                        @endif
                                    </select>

                                    <div class="credito-opciones" style="display:none; margin-top:8px;">
                                        @php
                                            $creditos = \App\Models\Credito::where('id_user', $usuario->id_user)->get();
                                        @endphp

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
                                <form action="{{ route('pedido.reabrir', $pedido->id_pedido) }}" method="POST">
                                    @csrf
                                    <button type="submit">Reabrir pedido</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach

            </tbody>
        </table>
    @else
        <p style="color: red;">No se encontraron pedidos.</p>
    @endif

    <br>
    <a href="{{ route('pedido.index') }}">← Volver a la lista de pedidos</a>
    
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
