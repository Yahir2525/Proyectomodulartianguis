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
    <title>Principal de Compras</title>
</head>
<body>
<section>
@foreach ($compraIndex as $compra)
                <center>
                <table>
                    <tr>
                        <th colspan="2">Tabla de la compra: {{ $compra->id_compra }}</th>
                    </tr>
                    <tr>
                        <th>Atributo</th>
                        <th>Valor</th>
                    </tr>
                    <tr>
                        <td>ID</td>
                        <td>{{ $compra->id }}</td>
                    </tr>
                    <tr>
                            <td>ID del Cliente</td>
                            <td>{{ optional($compra->cliente)->id ?? 'Sin cliente' }}</td>
                        </tr>
                    <tr>
                        <td>MONTO</td>
                        <td>{{ number_format($compra->total_pagar, 2) }}</td>
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