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
                        <td>{{ $compra->id_compra }}</td>
                    </tr>
                    <tr>
                            <td>Nombre del Cliente</td>
                            <td>{{ optional($compra->cliente)->nombre_usuario ?? 'Sin cliente' }}</td>
                        </tr>
                    <tr>
                        <td>MONTO</td>
                        <td>{{ number_format($compra->total_pagar, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Tipo</td>
                        <td>{{ $compra->created_at }}</td>
                    </tr>
                    <tr>
                        <td>Cantidad</td>
                        <td>{{ $compra->updated_at }}</td>
                    </tr>
                    
                </table>
                <br>
                <form action="{{ route('compra.destroy', $compra->id_compra) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <br><button type="submit" class="button is-danger">Eliminar Compra</button>
                        </form>
                        <br>
                @endforeach 
</section>
</body>
</html>