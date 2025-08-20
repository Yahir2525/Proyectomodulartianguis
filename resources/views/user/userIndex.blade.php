<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <link rel="stylesheet" href="{{ asset('css/user/userIndex.css') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Listado de Usuarios</title>
    <style>
        .rol-header {
            background-color: #343a40;
            color: white;
            padding: 10px;
            font-weight: bold;
            margin-top: 40px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        .badge {
            background-color: #007bff;
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.75rem;
            margin-right: 4px;
        }
        img {
            max-width: 80px;
            border-radius: 4px;
        }
        a.edit-link {
            color: #0d6efd;
            margin-right: 10px;
            text-decoration: none;
        }
        button.delete-btn {
            color: red;
            background: none;
            border: none;
            cursor: pointer;
        }
    </style>
</head>
<body>

<h1>Usuarios agrupados por rol</h1>

    @can('create user')
                <a href="{{ url('/user/create') }}">Registrar un nuevo usuario</a>
    @endcan

    <form action="{{ url('/user/showUser') }}" method="GET">
        <label for="busqueda">Buscar por ID o nombre de usuario:</label>
        <input 
            type="text" 
            id="busqueda" 
            name="busqueda" 
            placeholder="Ejemplo: 21 o car" 
            list="usuarios" 
            value="{{ request('busqueda') }}" 
            autocomplete="off"
            autofocus
        />

        <datalist id="usuarios">
            @foreach ($usuarios as $usuario)
                <option value="{{ $usuario->nombre_usuario }}"></option>
            @endforeach
        </datalist>

        <input type="submit" value="Buscar" />
    </form>



@php
    $administradores = $userIndex->filter(fn($u) => $u->hasRole('administrador'));
    $usuariosNormales = $userIndex->filter(fn($u) => !$u->hasRole('administrador'));
@endphp

@foreach (['Administradores' => $administradores, 'Usuarios normales' => $usuariosNormales] as $titulo => $grupo)
    @if ($grupo->isNotEmpty())
        <div class="rol-header">{{ $titulo }}</div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Imagen</th>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Usuario</th>
                    <th>Telefono</th>
                    <th>Direccion</th>
                    <th>Nivel</th>
                    <th>Roles</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($grupo as $user)
                    <tr>
                        <td>{{ $user->id_user }}</td>
                        <td>
                            @if (!empty($user->imagen))
                                <img src="{{ Storage::disk('s3')->url($user->imagen) }}" alt="Foto de perfil" width="250">
                            @else
                                <span>Sin imagen</span>
                            @endif
                        </td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->nombre_usuario }}</td>
                        <td>{{ $user->telefono }}</td>
                        <td>{{ $user->direccion }}</td>
                        <td>{{ $user->nivel_usuario}}</td>
                        <td>
                            @foreach ($user->getRoleNames() as $rolename)
                                <span class="badge">{{ $rolename }}</span>
                            @endforeach
                        </td>
                        <td>
                            @can('edit user')
                                <a class="edit-link" href="{{ route('user.edit', $user->id_user) }}">Editar</a>
                            @endcan

                            @can('delete user')
                                <form action="{{ route('user.destroy', $user->id_user) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('¿Eliminar usuario?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="delete-btn">Eliminar</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endforeach

</body>
</html>
