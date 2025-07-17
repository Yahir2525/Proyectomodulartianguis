<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del Pedido</title>
</head>
<body>
    <h1>Detalle del Pedido</h1>

    @if ($pedido)
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
                <tr>
                    <td>{{ $pedido->id_pedido }}</td>
                    <td>{{ optional($pedido->user)->nombre_usuario ?? 'Sin usuario' }}</td>
                    <td>{{ $pedido->metodo_pago }}</td>
                    <td>{{ $pedido->estado_pedido }}</td>
                    <td>{{ $pedido->id_credito ?? 'N/A' }}</td>
                    <td>{{ $pedido->total_pedido }}</td>
                    <td>{{ $pedido->created_at }}</td>
                    <td>{{ $pedido->updated_at }}</td>
                </tr>
            </tbody>
        </table>
    @else
        <p style="color: red;">El pedido no se encontró.</p>
    @endif

    <br>
    <a href="{{ route('pedido.index') }}">← Volver a la lista de pedidos</a>
</body>
</html>
