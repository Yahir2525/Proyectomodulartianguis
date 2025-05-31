<!-- <!DOCTYPE html>
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
            <a href="{{ url('/pedido/create') }}" class="button is-info is-fullwidth">
                Registrar una nueva compra
            </a><br><br>
            <form action="{{ url('/pedido/showPedido') }}" method="GET"> 
                <div class="sub">
                    <label for="id">ID de compra a buscar:</label>
                    <input type="text" id="id" name="id_pedido" placeholder="21" autofocus>
                </div><br><br>
                <input type="submit" id="enviar" name="enviar" value="buscar">
            </form>
            @if($pedidoIndex->isNotEmpty())
                <br><h2>Tablas de pedidos registrados</h2>
                <center>
                    <table border="1">
                        <tr>
                            <th>Pedido</th>
                            <th>Pedido</th>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio unitario</th>
                            <th>Subtotal</th>
                            <th>Creado</th>
                            <th>Actualizado</th>
                            <th>Acciones</th>
                        </tr>
                        @foreach ($pedidoIndex as $pedido)
                        <tr>
                            <td>{{ $pedido->id_pedido }}</td>
                            <td>{{ optional($pedido->producto) ? $pedido->id_producto : 'No tiene producto' }}</td>
                            <td>{{ number_format($pedido->cantidad, 2) }}</td>
                            <td>{{ optional($pedido->producto) ? $pedido->precio_unitario : 'No tiene precio' }}</td>
                            <td>{{ number_format($pedido->subtotal, 2) }}</td>
                            <td>{{ $pedido->created_at }}</td>
                            <td>{{ $pedido->updated_at }}</td>
                            <td>
                                <a href="{{ route('pedido.edit', $pedido->id_pedido) }}" class="button is-primary">Editar</a>
                            </td>
                        </tr>
                        @endforeach
                    </table>
                    <br><strong>Total a pagar: {{ number_format($pedidoIndex->sum('total_pagar'), 2) }}</strong></br>
                </center>
            @endif
        </div>
    </section>
</body>
</html> -->

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

            @php
                $idPedido = request('id_pedido');
                $totalPedido = request('total');
            @endphp

            <a href="{{ url('/pedido/create') }}" class="button is-info is-fullwidth">
                Registrar un nuevo pedido
            </a><br><br>

            <form action="{{ url('/pedido/showPedido') }}" method="GET"> 
                <div class="sub">
                    <label for="id">ID de compra a buscar:</label>
                    <input type="text" id="id" name="id_pedido" placeholder="21" autofocus>
                </div><br><br>
                <input type="submit" id="enviar" name="enviar" value="Buscar">
            </form>

            @if($pedidoIndex->isNotEmpty())
                <br><h2>Tabla de pedidos registrados</h2>
                <center>
                    <table border="1">
                        <tr>
                            <th>ID pedido</th>
                            <th>Nombre usuario</th>
                            <th>Total del pedido</th>
                            <th>Estado del pedido</th>
                            <th>Creado</th>
                            <th>Actualizado</th>
                            <th colspan="2">Acciones</th>
                        </tr>

                        @foreach ($pedidoIndex as $pedido)
                            <tr>
                                <td>{{ $pedido->id_pedido }}</td>
                                <td>{{ optional($pedido->user)->nombre_usuario ?? 'Sin cliente' }}</td>
                                <td>{{ $pedido->total_pedido }}</td>
                                <td>{{ $pedido->estado_pedido }}</td>
                                <td>{{ $pedido->created_at }}</td>
                                <td>{{ $pedido->updated_at }}</td>
                                <td>
                                    <a href="{{ route('pedido.edit', $pedido->id_pedido) }}?total={{ $pedido->total_pedido }}">Editar</a>
                                </td>
                                <td>
                                    <form action="{{ url('/pedido', $pedido->id_pedido) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="button is-danger">Eliminar Pedido</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </center>
            @endif
        </div>
    </section>
</body>
</html>
