<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Principal de pedidos</title>
</head>
<body>
    <section>
        <div>
            <h1>Principal de pedidos</h1>
            <br>

            @if($carroIndex->isNotEmpty())
                <table border="1" cellspacing="0" cellpadding="5">
                    <thead>
                        <tr>
                            <th>ID del carrito</th>
                            <th>Nombre del producto</th>
                            <th>Cantidad</th>
                            <th>Precio unitario</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($carroIndex as $carrito)
                            @foreach ($carrito->productos as $producto)
                                <tr>
                                    <td>{{ $carrito->id_carro }}</td>
                                    <td>{{ $producto->nombre }}</td>
                                    <td>{{ $producto->pivot->cantidad }}</td>
                                    <td>{{ $producto->precio_unitario }}</td>
                                    <td>{{ $producto->pivot->cantidad * $producto->precio_unitario }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>

                @php 
                    $totalCarrito = $carroIndex->flatMap->productos->sum(function($producto) {
                        return $producto->pivot->cantidad * $producto->precio_unitario;
                    });
                @endphp

                <p><strong>Total: {{ $totalCarrito }}</strong></p>
            @endif
        </div>
    </section>
</body>
</html>
