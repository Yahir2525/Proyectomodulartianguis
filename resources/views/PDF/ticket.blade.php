<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket Pedido</title>
    <style>
        body { font-family: monospace; font-size: 12px; }
        .center { text-align: center; }
        .line { border-top: 1px dashed #000; margin: 5px 0; }
    </style>
</head>
<body>
    <div class="center">
        <h3>Blancos Doña Colchas</h3>
        <p>Ticket de Pedido #{{ $pedido->id_pedido }}</p>
    </div>

    <div class="line"></div>

    <p><strong>Cliente:</strong> {{ $pedido->user->nombre_usuario }}</p>
    <p><strong>Fecha:</strong> {{ now()->format('d/m/Y H:i') }}</p>
    <p><strong>Método:</strong> {{ ucfirst($pedido->metodo_pago) }}</p>

    <div class="line"></div>

    <table width="100%">
        <thead>
            <tr>
                <th align="left">Producto</th>
                <th align="center">Cant</th>
                <th align="right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pedido->carro->productos as $prod)
                <tr>
                    <td>{{ $prod->nombre }}</td>
                    <td align="center">{{ $prod->pivot->cantidad }}</td>
                    <td align="right">${{ number_format($prod->pivot->cantidad * $prod->precio_unitario, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="line"></div>

    <p class="center"><strong>Total: ${{ number_format($pedido->total_pedido, 2) }}</strong></p>

    <div class="line"></div>
    <p class="center">¡Gracias por tu compra!</p>
</body>
</html>
