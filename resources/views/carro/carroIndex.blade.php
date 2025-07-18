<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Principal de carros</title>
    <center><h1>LOS MEJORES PROYECTOS NO COMO YAMORAS O JUANITOS PROYECT</h1></center>
    

    @php
    use App\Models\CarroProducto;
    @endphp
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
                        

                        // Calcular reservas globales desde la tabla pivote carro_productos
                        $reservasGlobales = CarroProducto::select('id_producto')
                            ->selectRaw('SUM(cantidad) as total_reservado')
                            ->groupBy('id_producto')
                            ->pluck('total_reservado', 'id_producto');

                        $carrosPorPedido = $carroIndex->groupBy('id_pedido');
                    @endphp

                    @foreach($carrosPorPedido as $idPedido => $carros)
                    @php
                        $hayProductos = false;
                        foreach ($carros as $carrito) {
                            if ($carrito->productos->isNotEmpty()) {
                                $hayProductos = true;
                                break;
                            }
                        }
                    @endphp

                    @if ($hayProductos)
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
                                    <th>Imagen</th>
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
                                            $stock = $producto->piezas;
                                            $reservado = $reservasGlobales[$producto->id_producto] ?? 0;
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
                                            <td>
                                                @if ($producto->imagen)
                                                    <img src="{{ asset($producto->imagen) }}" alt="Imagen del producto" width="250">
                                                @else
                                                    Sin imagen
                                                @endif
                                            </td>
                                            <td>{{ $disponible }}</td>
                                            <td>{{ $producto->pivot->cantidad }}</td>
                                            <td>{{ $producto->precio_unitario }}</td>
                                            <td>{{ $subtotal }}</td>
                                            <td>
                                                @if($carrito->pedido && $carrito->pedido->estado_pedido == 1)
                                                    <a href="{{ route('carro.edit', ['id_carro' => $carrito->id_carro, 'id_producto' => $producto->id_producto]) }}">
                                                        <button type="button">Editar</button>
                                                    </a>
                                                    <form action="{{ route('carro.eliminarProducto', ['id_carro' => $carrito->id_carro, 'id_producto' => $producto->id_producto]) }}" method="POST" onsubmit="return confirm('¿Estás seguro?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit">Eliminar producto</button>
                                                    </form>
                                                @else
                                                    <span style="color: gray;">Pedido cerrado</span>
                                                @endif
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
                    @endif
                @endforeach

                @else
                    <p>No hay productos en el carrito.</p>
                @endif
            @endif
        </div>
    </section>
</body>
</html>
