<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/permission/permissionIndex.css') }}">
    <title>Página de roles</title>
</head>
<body>
<div class="page-container">
<main class="content">
<br>
    <div>
        <a href="{{ url('role') }}">Roles</a> |
        <a href="{{ url('permission') }}">Permissions</a> |
        <a href="{{ url('user') }}">Users</a>
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

        <br><hr class="hr-grueso"><center><h1>Principal roles</h1></center><hr class="hr-grueso"><br>

        @can('create permission')
            <a href="{{ url('/permission/create') }}">Registrar nuevo permiso</a><br><br>
        @endcan

        <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($permissionIndex as $permission)
                    <tr>
                        <td>{{ $permission->id }}</td>
                        <td>{{ $permission->name }}</td>
                        <td>
                            @can('edit permission')
                                <a href="{{ route('permission.edit', $permission->id) }}">Editar</a>
                            @endcan

                            @can('delete permission')
                                <form action="{{ route('permission.destroy', $permission->id) }}" method="POST" style="display:inline;">
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
    </div>
</main>
<x-footer/>
</div>
</body>
</html>
