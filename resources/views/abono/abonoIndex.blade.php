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
    <title>Principal de Abonos</title>
</head>
<body>
<section>
@foreach ($abonoIndex as $abono)
                        <center>
                        <table>
                    <tr>
                        <th colspan="2">Tabla del aceite: {{ $abono->id_abono }}</th>
                    </tr>
                    <tr>
                        <th>Atributo</th>
                        <th>Valor</th>
                    </tr>
                    <tr>
                        <td>ID</td>
                        <td>{{ $abono->id }}</td>
                    </tr>
                    <tr>
                            <td>Nombre de usuario</td>
                            <td>{{ optional($abono->cliente)->nombre_usuario ?? 'Sin cliente' }}</td>
                        </tr>
                    <tr>
                        <td>MONTO</td>
                        <td>{{ $abono->monto_abono }}</td>
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