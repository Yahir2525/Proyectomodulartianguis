<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<th colspan="2">Tabla del user: {{ $user->id_user }}</th>
<center>
                    <table>
                    <tr>
                        <th colspan="2">Tabla del user: {{ $user->id_user }}</th>
                    </tr>
                    <tr>
                        <th>Atributo</th>
                        <th>Valor</th>
                    </tr>
                    <tr>
                        <td>ID</td>
                        <td>{{ $user->id_user }}</td>
                    </tr>
                    <tr>
                        <td>Nombre</td>
                        <td>{{ $user->name }}</td>
                    </tr>
                    <tr>
                        <td>Correo</td>
                        <td>{{ $user->email }}</td>
                    </tr>
                    <tr>
                        <td>Genero</td>
                        <td>{{ $user->genero }}</td>
                    </tr>

                    <tr>
                        <td>Edad</td>
                        <td>{{ $user->edad }}</td>
                    </tr>
                    <tr>
                        <td>Telefono</td>
                        <td>{{ $user->telefono }}</td>
                    </tr>
                    <tr>
                        <td>Direccion</td>
                        <td>{{ $user->direccion }}</td>
                    </tr>
                    <tr>
                        <td>Nombre_usuario</td>
                        <td>{{ $user->nombre_usuario }}</td>
                    </tr>
                    <tr>
                        <td>Creacion</td>
                        <td>{{ $user->created_at }}</td>
                    </tr>
                    <tr>
                        <td>Actualización</td>
                        <td>{{ $user->updated_at }}</td>
                    </tr>
                </table>
</body>
</html>