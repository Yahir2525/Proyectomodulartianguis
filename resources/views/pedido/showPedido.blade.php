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
                @endforeach
            </tbody>
        </table>
    @else
        <p style="color: red;">No se encontraron pedidos.</p>
    @endif

    <br>
    <a href="{{ route('pedido.index') }}">← Volver a la lista de pedidos</a>
</body>
</html>
