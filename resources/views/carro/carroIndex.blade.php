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
            <h1>Principal de carros</h1>
            @if(Auth::check())
            <p>
                <a href="{{ url('/carro/create') }}">Registrar un nuevo carro</a>
            </p>

            <form action="{{ url('/carro/showCarro') }}" method="GET">
                <label for="id">ID de carro a buscar:</label>
                <input type="text" id="id" name="id_carro" placeholder="21" autofocus>
                <input type="submit" value="Buscar">
            </form>
            <br><br>
            @if($carroIndex->isNotEmpty())
                @php
                    // Inicializar el acumulador de reservas
                    $reservasAcumuladas = [];

                    // Agrupar carritos por id_pedido (por si no lo hiciste en el controlador)
                    $carrosPorPedido = $carroIndex->groupBy('id_pedido');
                @endphp

                @foreach($carrosPorPedido as $idPedido => $carros)
                    <h2>Numero de pedido #{{ $idPedido }}</h2>

                    <table border="1" cellspacing="0" cellpadding="5">
                        <thead>
                            <tr>
                                <th>ID del carrito</th>
                                <th>ID del usuario</th>
                                <th>ID del pedido</th>
                                <th>ID del producto</th>
                                <th>Nombre del producto</th>
                                <th>Estado del producto</th>
                                <th>Cantidad</th>
                                <th>Precio unitario</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalPedido = 0;
                            @endphp
                            @foreach ($carros as $carrito)
                                @foreach ($carrito->productos as $producto)
                                    @php
                                        $id = $producto->id_producto;
                                        $stockOriginal = $producto->piezas;

                                        $reservadoAntes = $reservasAcumuladas[$id] ?? 0;
                                        $piezas_disponibles = $stockOriginal - $reservadoAntes;

                                        $reservasAcumuladas[$id] = $reservadoAntes + $producto->pivot->cantidad;

                                        $subtotal = $producto->pivot->cantidad * $producto->precio_unitario;
                                        $totalPedido += $subtotal;
                                    @endphp
                                    <tr>
                                        <td>{{ $carrito->id_carro }}</td>
                                        <td>{{ $carrito->id_user }}</td>
                                        <td>{{ $carrito->id_pedido }}</td>
                                        <td>{{ $producto->id_producto }}</td>
                                        <td>{{ $producto->nombre }}</td>
                                        <td>{{ $piezas_disponibles }}</td>
                                        <td>{{ $producto->pivot->cantidad }}</td>
                                        <td>{{ $producto->precio_unitario }}</td>
                                        <td>{{ $subtotal }}</td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>

                    <p><strong>Total del Pedido #{{ $idPedido }}: {{ $totalPedido }}</strong></p>

                    <!-- Formulario para finalizar este pedido -->
                    <form action="{{ route('pedido.update', $idPedido) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="total" value="{{ $totalPedido }}">
                        <button type="submit">Actualizar total y ver pedidos</button>
                    </form>
                    <hr>
                @endforeach

            @else
                <p>No hay productos en el carrito.</p>
            @endif
            @endif
        </div>
    </section>
</body>
</html>
