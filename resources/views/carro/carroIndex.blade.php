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
                        // Inicializar acumulador global para reservas por producto
                        $reservasGlobales = [];

                        // Llenar reservas globales: suma total de cantidades reservadas por producto en todos los carritos
                        foreach ($carroIndex as $carrito) {
                            foreach ($carrito->productos as $producto) {
                                $idProd = $producto->id_producto;
                                $cantidad = $producto->pivot->cantidad;
                                if (!isset($reservasGlobales[$idProd])) {
                                    $reservasGlobales[$idProd] = 0;
                                }
                                $reservasGlobales[$idProd] += $cantidad;
                            }
                        }

                        // Agrupar carritos por id_detalle
                        $carrosPorDetalle = $carroIndex->groupBy('id_detalle');
                    @endphp

                    @foreach($carrosPorDetalle as $idDetalle => $carros)
                        <h2>Numero de pedido #{{ $idDetalle }}</h2>

                        <table border="1" cellspacing="0" cellpadding="5">
                            <thead>
                                <tr>
                                    <th>ID del carrito</th>
                                    <th>ID del usuario</th>
                                    <th>Nombre del usuario</th>
                                    <th>ID del detalle</th>
                                    <th>ID del producto</th>
                                    <th>Nombre del producto</th>
                                    <th>Piezas disponibles</th>
                                    <th>Cantidad</th>
                                    <th>Precio unitario</th>
                                    <th>Subtotal</th>
                                    <th>Editar</th>
                                    <th>Eliminar</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $totalPedido = 0; @endphp
                                @foreach ($carros as $carrito)
                                    @foreach ($carrito->productos as $producto)
                                        @php
                                            $id = $producto->id_producto;
                                            $stockOriginal = $producto->piezas;

                                            // Resta el total reservado globalmente para este producto
                                            $reservadoGlobal = $reservasGlobales[$id] ?? 0;
                                            $piezas_disponibles = $stockOriginal - $reservadoGlobal;

                                            $subtotal = $producto->pivot->cantidad * $producto->precio_unitario;
                                            $totalPedido += $subtotal;
                                        @endphp
                                        <tr>
                                            <td>{{ $carrito->id_carro }}</td>
                                            <td>{{ $carrito->id_user }}</td>
                                            <td>{{ optional($carrito->user)->nombre_usuario ?? 'Sin cliente' }}</td>
                                            <td>{{ $carrito->id_detalle }}</td>
                                            <td>{{ $producto->id_producto }}</td>
                                            <td>{{ $producto->nombre }}</td>
                                            <td>{{ $piezas_disponibles }}</td>
                                            <td>{{ $producto->pivot->cantidad }}</td>
                                            <td>{{ $producto->precio_unitario }}</td>
                                            <td>{{ $subtotal }}</td>
                                            <td>
                                                <a href="{{ route('carro.edit', $carrito->id_carro) }}">Editar</a>
                                            </td>
                                            <td>
                                                <form action="{{ url('/carro', $carrito->id_carro) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit">Eliminar</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                        <p><strong>Total del Detalle #{{ $idDetalle }}: {{ $totalPedido }}</strong></p>

                        <!-- Formulario para finalizar este pedido -->
                        <form action="{{ route('detalle.update', $idDetalle) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="total" value="{{ $totalPedido }}">
                            <button type="submit">Actualizar total y ver detalle</button>
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
