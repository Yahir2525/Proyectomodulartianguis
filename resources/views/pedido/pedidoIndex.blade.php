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
            <a href="{{ url('/pedido/create') }}" class="button is-info is-fullwidth">
                Registrar una nuevo pedido
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
                @php $comprasAgrupadas = $pedidoIndex->groupBy('id_compra'); @endphp
                
                @foreach ($comprasAgrupadas as $id_compra => $pedidos)
                    <center>
                        <h3>Pedidos de la Compra ID: {{ $id_compra }}</h3>
                        <table border="1">
                            <tr>
                                <th>Pedido</th>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Precio unitario</th>
                                <th>Subtotal</th>
                                <th>Creado</th>
                                <th>Actualizado</th>
                                <th>Acciones</th>
                            </tr>
                            @php $totalCompra = 0; @endphp  <!-- Variable para sumar los subtotales de la compra -->
                            @foreach ($pedidos as $pedido)
                                <tr>
                                    <td>{{ $pedido->id_pedido }}</td>
                                    <td>{{ optional($pedido->producto) ? $pedido->id_producto : 'No tiene producto' }}</td>
                                    <td>
                                        <form action="" method="POST">
                                            @csrf
                                            @method('GET')
                                            <input type="number" name="cantidad" value="{{ number_format($pedido->cantidad, 2) }}" style="width: 50px;" min="1" step="1" required>
                                        </form>
                                    </td>
                                    <td>{{ optional($pedido->producto) ? $pedido->precio_unitario : 'No tiene precio' }}</td>
                                    <td>{{ number_format($pedido->subtotal, 2) }}</td>
                                    <td>{{ $pedido->created_at }}</td>
                                    <td>{{ $pedido->updated_at }}</td>
                                    <td>
                                        <a href="{{ route('pedido.edit', $pedido->id_pedido) }}" class="button is-primary">Editar</a>
                                    </td>
                                    
                                </tr>
                                @php $totalCompra += $pedido->subtotal; @endphp  <!-- Sumar el subtotal de cada pedido -->
                                <form action="{{ url('/pedido', $pedido->id_pedido) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <br><button type="submit" class="button is-danger">Eliminar Pedido</button>
                                </form>
                            @endforeach
                        </table>
                        <br><strong>Total a pagar por esta compra: {{ number_format($totalCompra, 2) }}</strong><br><br>
                    </center>
                @endforeach
                
            @endif
        </div>
    </section>
</body>
</html>

