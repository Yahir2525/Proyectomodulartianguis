<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/role/roleIndex.css') }}">
    <title>Listado de roles</title>
</head>
<body>
    <div class="page-container">
        <main class="content">
        <br><x-barraadmin/>
            <section class="container">
                <br><hr class="hr-grueso"><center><h1>Listado de roles</h1></center><hr class="hr-grueso"><br>

                @can('create role')
                    <a class="btn btn-primary mb-3" href="{{ url('/role/create') }}">
                        Registrar un nuevo rol
                    </a>
                @endcan

                @foreach ($roleIndex as $role)
                    <h2>Rol #{{ $role->id }} — {{ $role->name }}</h2>

                    <div class="card mb-4">
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID Permiso</th>
                                        <th>Permiso</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($role->permissions as $permission)
                                        <tr>
                                            <td data-label="ID Permiso">{{ $permission->id }}</td>
                                            <td data-label="Permiso">
                                                <div class="permiso-chip">
                                                    {{ $permission->name }}

                                                    @can('edit role')
                                                        <form action="{{ route('role.permission.destroy', [$role->id, $permission->id]) }}"
                                                                method="POST"
                                                                onsubmit="return confirm('¿Quitar el permiso \"{{ $permission->name }}\" del rol \"{{ $role->name }}\"?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" title="Eliminar">
                                                                <i class="fa fa-times"></i>
                                                            </button>
                                                        </form>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-muted text-center">
                                                Este rol no tiene permisos asignados.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-3">
                        <center>
                            @can('edit role')
                                <a class="btn btn-edit" href="{{ route('role.edit', $role->id) }}">
                                    Editar
                                </a>
                            @endcan

                            @can('delete role')
                                <form action="{{ route('role.destroy', $role->id) }}"
                                        method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('¿Eliminar el rol \"{{ $role->name }}\"?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        Eliminar
                                    </button>
                                </form>
                            @endcan
                        </center>
                    </div>
                @endforeach

                @if ($roleIndex->isEmpty())
                    <p class="text-muted mt-3">No hay roles para mostrar.</p>
                @endif
                
            </section>
        </main>
        <x-footer/>
    </div>
</body>
</html>
