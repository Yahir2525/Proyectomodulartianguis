<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página de roles</title>
</head>
<body>
    <div>
        <a href="{{ url('role') }}">Roles</a> |
        <a href="{{ url('permission') }}">Permisos</a> |
        <a href="{{ url('user') }}">Usuarios</a>
    </div>

    <div>
        @if (session('status'))
            <p>{{ session('status') }}</p>
        @endif

        @if (Auth::check())
            <p>Sesión iniciada por: {{ Auth::user()->name }}</p>
        @else
            <p>No hay sesión activa.</p>
        @endif

        <h2>Listado de Roles y Permisos</h2>

        @can('create role')
            <a href="{{ url('/user/create') }}">Registrar nuevo rol</a><br><br>
        @endcan

        <table border="1" cellspacing="0" cellpadding="8">
            <thead>
                <tr>
                    <th>ID Rol</th>
                    <th>Nombre Rol</th>
                    <th>Permiso</th>
                    <th>Quitar Permiso</th>
                    <th>Editar Rol</th>
                    <th>Eliminar Rol</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($roleIndex as $role)
                    @forelse ($role->permissions as $permission)
                        <tr>
                            <td>{{ $role->id }}</td>
                            <td>{{ $role->name }}</td>
                            <td>{{ $permission->name }}</td>
                            <td>
                                <form action="{{ route('role.permission.destroy', [$role->id, $permission->id]) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit">Quitar</button>
                                </form>
                            </td>
                            <td>
                                @can('edit role')
                                    <a href="{{ route('role.edit', $role->id) }}">Editar</a>
                                @endcan
                            </td>
                            <td>
                                @if ($loop->first)
                                    <form action="{{ route('role.destroy', $role->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit">Eliminar</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td>{{ $role->id }}</td>
                            <td>{{ $role->name }}</td>
                            <td colspan="4">Este rol no tiene permisos asignados.</td>
                        </tr>
                    @endforelse
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>


<!-- 
esto es para no repetir rol por cada permiso


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página de roles</title>
</head>
<body>
    <div style="margin-bottom: 20px;">
        <a href="{{ url('role') }}">Roles</a> |
        <a href="{{ url('permission') }}">Permisos</a> |
        <a href="{{ url('user') }}">Usuarios</a>
    </div>

    <div>
        @if (session('status'))
            <p><strong>{{ session('status') }}</strong></p>
        @endif

        @if (Auth::check())
            <p>Sesión iniciada por: <strong>{{ Auth::user()->name }}</strong></p>
        @else
            <p><strong>No hay sesión activa.</strong></p>
        @endif

        <h2>Roles</h2>

        @can('create role')
            <p><a href="{{ url('/user/create') }}">Registrar nuevo rol</a></p>
        @endcan

        <table border="1" cellspacing="0" cellpadding="8" style="width: 100%; text-align: left;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Permisos</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($roleIndex as $role)
                    <tr>
                        <td>{{ $role->id }}</td>
                        <td>{{ $role->name }}</td>
                        <td>
                            @if ($role->permissions->isNotEmpty())
                                {{ $role->permissions->pluck('name')->join(', ') }}
                            @else
                                Sin permisos asignados
                            @endif
                        </td>
                        <td>
                            @can('edit role')
                                <a href="{{ route('role.edit', $role->id) }}">Editar</a>
                            @endcan

                            @can('delete role')
                                <form action="{{ route('role.destroy', $role->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit">Eliminar</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html> -->
