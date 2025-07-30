<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Usuarios encontrados</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        h2 {
            margin-bottom: 20px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 30px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
            vertical-align: middle;
        }
        th {
            background-color: #f2f2f2;
        }
        img {
            max-width: 100px;
            border-radius: 4px;
        }
        a {
            color: #0d6efd;
            text-decoration: none;
            margin-right: 10px;
        }
        button {
            background: none;
            border: none;
            color: red;
            cursor: pointer;
        }
    </style>
</head>
<body>

<h2>Usuarios encontrados ({{ $usuarios->count() }})</h2>

@if($usuarios->isEmpty())
    <p>No se encontraron usuarios con los datos ingresados.</p>
@else
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Imagen</th>
            <th>Correo</th>
            <th>Género</th>
            <th>Edad</th>
            <th>Teléfono</th>
            <th>Dirección</th>
            <th>Nombre de usuario</th>
            <th>Creado</th>
            <th>Actualizado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($usuarios as $user)
            <tr>
                <td>{{ $user->id_user }}</td>
                <td>{{ $user->name }}</td>
                <td>
                    @if($user->imagen)
                        <img src="{{ asset($user->imagen) }}" alt="Imagen de perfil">
                    @else
                        <span>Sin imagen</span>
                    @endif
                </td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->genero }}</td>
                <td>{{ $user->edad ?? 'No registrada' }}</td>
                <td>{{ $user->telefono ?? 'No registrado' }}</td>
                <td>{{ $user->direccion ?? 'No registrada' }}</td>
                <td>{{ $user->nombre_usuario }}</td>
                <td>{{ $user->created_at }}</td>
                <td>{{ $user->updated_at }}</td>
                <td>
                    <a href="{{ route('user.edit', $user->id_user) }}">Editar</a>

                    <form action="{{ route('user.destroy', $user->id_user) }}" method="POST" style="display:inline;" onsubmit="return confirm('¿Seguro que deseas eliminar este usuario?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit">Eliminar</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
@endif

</body>
</html>
