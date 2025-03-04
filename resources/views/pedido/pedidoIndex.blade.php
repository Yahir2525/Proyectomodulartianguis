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
                @foreach ($pedidoIndex as $pedido)
                    <center>
                        <table>
                            <tr>
                                <th colspan="2">Tabla del pedido: {{ $pedido->id_pedido }}</th>
                            </tr>
                            <tr>
                                <th>Atributo</th>
                                <th>Valor</th>
                            </tr>
                            <tr>
                                <td>ID del pedido</td>
                                <td>{{ $pedido->id_pedido }}</td>
                            </tr>
                            <tr>
                                <td>ID de la compra</td>
                                <td>{{ optional($pedido->compra)->id_compra ?? 'Sin compra' }}</td>
                            </tr>
                            <tr>
                                <td>ID del producto</td>
                                <td>{{ optional ($pedido->producto) ? $pedido->id_producto : 'No tiene producto'}}</td>
                            </tr>
                            <tr>
                                <td>Cantidad de producto</td>
                                <td>{{ number_format($pedido->cantidad, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Precio unitario del producto</td>
                                <td>{{ optional($pedido->producto) ? $pedido->precio_unitario : 'No tiene precio' }}</td>
                            </tr>
                            <tr>
                                <td>Subtotal</td>
                                <td>{{ number_format($pedido->subtotal, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Total a pagar</td>
                                <td>{{ number_format($pedido->total_pagar, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Creado</td>
                                <td>{{ $pedido->created_at }}</td>
                            </tr>
                            <tr>
                                <td>Actualizado</td>
                                <td>{{ $pedido->updated_at }}</td>
                            </tr>
                        </table>
                        <br>
                        <a href="{{ route('pedido.edit', $pedido->id_pedido) }}" class="button is-primary">Editar Compra</a>
                    </center>
                @endforeach 
            @endif
        </div>
    </section>
</body>
</html>
