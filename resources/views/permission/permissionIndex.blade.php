<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/permission/permissionIndex.css') }}">
    <title>Listado de permisos</title>
</head>
<body>
<div class="page-container">
<main class="content">
<br><x-barraadmin/>
<section class="container">
    <div>
        @if (session('status'))
            <p>{{ session('status') }}</p>
        @endif


        <br><hr class="hr-grueso"><center><h1>Listado de permisos</h1></center><hr class="hr-grueso"><br>

        @can('create permission')
            <a class="btn btn-primary mb-3" href="{{ url('/permission/create') }}">
                Registrar nuevo permiso
            </a>
        @endcan

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Permiso</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($permissionIndex as $permission)
                        <tr>
                            <td data-label="ID">{{ $permission->id }}</td>
                            <td data-label="Permiso">
                                <div class="permiso-chip">
                                    {{ $permission->name }}
                                    @can('delete permission')
                                        <form action="{{ route('permission.destroy', $permission->id) }}" 
                                              method="POST" 
                                              onsubmit="return confirm('¿Eliminar el permiso \"{{ $permission->name }}\"?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Eliminar">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                            <td data-label="Acciones">
                                @can('edit permission')
                                    <a class="btn btn-edit" href="{{ route('permission.edit', $permission->id) }}">
                                        Editar
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-muted text-center">No hay permisos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
</main>
<x-footer/>
</div>
</body>
</html>
