<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <link rel="icon" type="image/x-icon" href="/img/yourico.ico" />
    <link rel="stylesheet" href="{{ asset('css/aceite/indexAceite.css') }}">
    <link href="https://fonts.googleapis.com/css?family=Questrial&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/bulma@0.9.4/css/bulma.min.css" />
    <link rel="stylesheet" type="text/css" href="{{ asset('css/aceite/indexAceite.css') }}"> -->
    <title>Principal de Pedidos</title>
</head>
<body>
<section>
@foreach ($pedidoIndex as $pedido)
                <center>
                <table>
                    <tr>
                        <th colspan="2">Tabla de la compra: {{ $pedido->id_pedido }}</th>
                    </tr>
                    <tr>
                        <th>Atributo</th>
                        <th>Valor</th>
                    </tr>
                    <tr>
                        <td>ID del pedido</td>
                        <td>{{ $pedido->id }}</td>
                    </tr>
                    <tr>
                            <td>ID de la compra </td>
                            <td>{{ optional($pedido->compra)->id ?? 'Sin compra' }}</td>
                        </tr>
                    <tr>
                    <tr>
                            <td>ID del producto </td>
                            <td>{{ optional($pedido->producto)->id ?? 'Sin producto' }}</td>
                        </tr>
                    <tr>
                        <td>Cantidad de producto</td>
                        <td>{{ number_format($pedido->cantidad, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Precio unitario del prodcuto</td>
                        <td>{{ optional($pedido->producto)->precio_unitario ?? 'Sin precio del producto' }}</td>
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
                        <td>Tipo</td>
                        <td>{{ $abono->created_at }}</td>
                    </tr>
                    <tr>
                        <td>Cantidad</td>
                        <td>{{ $abono->updated_at }}</td>
                    </tr>
                </table>
                @endforeach 
</section>
</body>
</html>