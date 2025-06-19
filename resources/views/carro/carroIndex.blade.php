<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Principal de carros</title>
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
                    <input type="text" id="id" name="id_carro" placeholder="21" autofocus />
                    <input type="submit" value="Buscar" />
                </form>
                <br /><br />

                @if($carroIndex->isNotEmpty())
                    @php
                        // Calcular reservas globales (suma total de piezas reservadas por todos los usuarios por producto)
                        $reservasGlobales = \App\Models\Carro::select('id_producto')
                        ->selectRaw('SUM(cantidad) as total_reservado')
                        ->groupBy('id_producto')
                        ->pluck('total_reservado', 'id_producto');
                        $carrosPorPedido = $carroIndex->groupBy('id_pedido');
                    @endphp

                    @foreach($carrosPorPedido as $idPedido => $carros)
                        <h2>Pedido #{{ $idPedido }}</h2>

                        <table border="1" cellspacing="0" cellpadding="5">
                            <thead>
                                <tr>
                                    <th>ID del carrito</th>
                                    <th>ID del usuario</th>
                                    <th>Nombre del usuario</th>
                                    <th>ID del pedido</th>
                                    <th>ID del producto</th>
                                    <th>Nombre del producto</th>
                                    <th>Piezas disponibles</th>
                                    <th>Cantidad</th>
                                    <th>Precio unitario</th>
                                    <th>Subtotal</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $totalPedido = 0; @endphp
                                @foreach ($carros as $carrito)
                                    @foreach ($carrito->productos as $producto)
                                        @php
                                            $stock = $producto->piezas; // inventario total
                                            $reservado = $reservasGlobales[$producto->id_producto] ?? 0; // suma de todas las cantidades reservadas
                                            $disponible = max(0, $stock - $reservado);

                                            $subtotal = $producto->pivot->cantidad * $producto->precio_unitario;
                                            $totalPedido += $subtotal;
                                        @endphp
                                        <tr>
                                            <td>{{ $carrito->id_carro }}</td>
                                            <td>{{ $carrito->id_user }}</td>
                                            <td>{{ optional($carrito->user)->nombre_usuario ?? 'Sin cliente' }}</td>
                                            <td>{{ $carrito->id_pedido }}</td>
                                            <td>{{ $producto->id_producto }}</td>
                                            <td>{{ $producto->nombre }}</td>
                                            <td>{{ $disponible }}</td>
                                            <td>{{ $producto->pivot->cantidad }}</td>
                                            <td>{{ $producto->precio_unitario }}</td>
                                            <td>{{ $subtotal }}</td>
                                            <td>
                                                <div style="display: flex; gap: 5px; justify-content: center;">
                                                    <!-- Botón Editar -->
                                                    <a href="{{ route('carro.edit', $carrito->id_carro) }}">
                                                        <button type="button">Editar</button>
                                                    </a>

                                                    <!-- Botón Eliminar con confirmación -->
                                                    <form action="{{ url('/carro', $carrito->id_carro) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este carrito?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit">Eliminar</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>

                        <p><strong>Total del pedido #{{ $idPedido }}: {{ $totalPedido }}</strong></p>

                        <form action="{{ route('pedido.update', $idPedido) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="total" value="{{ $totalPedido }}">
                            <button type="submit">Actualizar total y ver pedido</button>
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
