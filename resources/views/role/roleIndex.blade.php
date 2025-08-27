<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/role/roleIndex.css') }}">
    <title>Principal de roles</title>
</head>
<body>
<br>
<div class="container">

    <nav class="top-nav">
        <a href="{{ url('role') }}">Roles</a>
        <a href="{{ url('permission') }}">Permisos</a>
        <a href="{{ url('user') }}">Usuarios</a>
    </nav>

    @if (session('status'))
        <div class="alert info">{{ session('status') }}</div>
    @endif
    @if (session('success'))
        <div class="alert success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert danger">{{ session('error') }}</div>
    @endif

    @if (Auth::check())
        <p class="session">Sesión iniciada por: <strong>{{ Auth::user()->name }}</strong></p>
    @else
        <p class="session"><strong>No hay sesión activa.</strong></p>
    @endif

    <header class="page-header">
        <br><hr class="hr-grueso"><center><h1>Listado de roles</h1></center><hr class="hr-grueso"><br>
        @can('create role')
            <a class="btn primary" href="{{ url('/role/create') }}">Registrar nuevo rol</a>
        @endcan
    </header>

    @forelse ($roleIndex as $role)
        <section class="role-card">
            <div class="role-card__head">
                <div>
                    <h2 class="role-title">Rol #{{ $role->id }} — {{ $role->name }}</h2>
                    <p class="muted">
                        @if($role->permissions->isNotEmpty())
                            {{ $role->permissions->count() }} permiso(s) asignado(s)
                        @else
                            Sin permisos asignados
                        @endif
                    </p>
                </div>

                <div class="role-actions">
                    @can('edit role')
                        <a class="btn" href="{{ route('role.edit', $role->id) }}">Editar</a>
                    @endcan

                    @can('delete role')
                        <form action="{{ route('role.destroy', $role->id) }}" method="POST" onsubmit="return confirm('¿Eliminar el rol \"{{ $role->name }}\"?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn danger">Eliminar</button>
                        </form>
                    @endcan
                </div>
            </div>

            <div class="table-wrap">
                <table class="perm-table">
                    <thead>
                        <tr>
                            <th style="width:70%">Permiso</th>
                            <th style="width:30%">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($role->permissions as $permission)
                            <tr>
                                <td>{{ $permission->name }}</td>
                                <td>
                                    @can('edit role')
                                        <form action="{{ route('role.permission.destroy', [$role->id, $permission->id]) }}" method="POST" onsubmit="return confirm('¿Quitar el permiso \"{{ $permission->name }}\" del rol \"{{ $role->name }}\"?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn ghost">Quitar</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="muted">Este rol no tiene permisos asignados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @empty
        <p class="muted">No hay roles para mostrar.</p>
    @endforelse>

</div>
</body>
</html>
