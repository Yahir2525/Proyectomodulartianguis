<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Principal de users</title>
</head>
<body>
    <section>
        <div>
            <h1>Principal de users</h1>
            <br>
            <a href="{{ url('/user/create') }}" class="button is-info is-fullwidth">
                Registrar una nueva compra
            </a><br><br>
            <form action="{{ url('/user/showUser') }}" method="GET"> 
                <div class="sub">
                    <label for="id">ID de compra a buscar:</label>
                    <input type="text" id="id_user" name="id_user" placeholder="21" autofocus>
                </div><br><br>
                <input type="submit" id="enviar" name="enviar" value="buscar">
            </form>
            @if($userIndex->isNotEmpty())
                <br><h2>Tablas de users registrados</h2>
                @foreach ($userIndex as $user)
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
                        <br>
                        <a href="{{ route('user.edit', $user->id_user) }}" class="button is-primary">Editar Cliente</a>
                        <form action="{{ route('user.destroy', $user->id_user) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <br><button type="submit" class="button is-danger">Eliminar user</button>
                        </form>
                    </center>
                @endforeach 
            @endif
        </div>
    </section>
</body>
</html>