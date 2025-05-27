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
                            <th>ID del producto</th>
                            <th>Nombre del producto</th>
                            <th>Estado del producto</th>
                            <th>Cantidad</th>
                            <th>Precio unitario</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($carroIndex as $carrito)
                            @foreach ($carrito->productos as $producto)

                            
                            @php
                            $piezas_disponibles = $producto->piezas - $producto->pivot->cantidad;
<!-- HACER QUE SE HAGA BIEN LA SUMA DE PIEZAS DISPONIBLES -140 -->
                            @endphp
                                <tr>
                                    <td>{{ $carrito->id_carro }}</td>
                                    <td>{{ $producto->id_producto }}</td>
                                    <td>{{ $producto->nombre }}</td>
                                    <td>{{ $piezas_disponibles}}</td>
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

                <!-- Formulario para finalizar la compra -->
                <form action="{{ url('carro') }}" method="POST">
                    @csrf
                    <input type="hidden" name="total_compra" value="{{ $totalCarrito }}">
                    <button type="submit">Finalizar compra</button>
                </form>
            @else
                <p>No hay productos en el carrito.</p>
            @endif
        </div>
    </section>
</body>
</html>

