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
    <title>Principal de clientes</title>
</head>
<body>
<section>
@foreach ($clienteIndex as $cliente)
                <center>
                <table>
                    <tr>
                        <th colspan="2">Tabla del cliente: {{ $cliente->id_cliente }}</th>
                    </tr>
                    <tr>
                        <th>Atributo</th>
                        <th>Valor</th>
                    </tr>
                    <tr>
                        <td>ID</td>
                        <td>{{ $cliente->id }}</td>
                    </tr>
                    <tr>
                        <td>Nombre</td>
                        <td>{{ $cliente->nombre }}</td>
                    </tr>
                    <tr>
                        <td>Genero</td>
                        <td>{{ $cliente->genero }}</td>
                    </tr>
                    <tr>
                        <td>Edad</td>
                        <td>{{ $cliente->edad }}</td>
                    </tr>
                    <tr>
                        <td>Telefono</td>
                        <td>{{ $cliente->telefono }}</td>
                    </tr>
                    <tr>
                        <td>Direccion</td>
                        <td>{{ $cliente->direccion }}</td>
                    </tr>
                    <tr>
                        <td>Correo</td>
                        <td>{{ $cliente->correo}}</td>
                    </tr>
                    <tr>
                        <td>Nombre_usuario</td>
                        <td>{{ $cliente->nombre_usuario }}</td>
                    </tr>
                    <tr>
                        <td>Creacion</td>
                        <td>{{ $cliente->created_at }}</td>
                    </tr>
                    <tr>
                        <td>Actualización</td>
                        <td>{{ $cliente->updated_at }}</td>
                    </tr>
                </table>
                @endforeach 
</section>
</body>
</html>