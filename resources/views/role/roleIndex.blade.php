<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paginas de roles</title>
</head>
<body>
<div class="container mt-5">
        <a href="{{ url('role') }}" class="btn btn-primary mx-1">Roles</a>
        <a href="{{ url('permission') }}" class="btn btn-info mx-1">Permissions</a>
        <a href="{{ url('user') }}" class="btn btn-warning mx-1">Users</a>
    </div>

    <div class="container mt-2">
        <div class="row">
            <div class="col-md-12">

                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif

                @if (Auth::check())
                <p>Sesión iniciada por: {{ Auth::user()->name }}</p>
                @else
                <p>No hay sesión activa.</p>
                @endif

                <div class="card mt-3">
                    <div class="card-header">
                        <h4>
                            Roles
                            @can('create role')
                            <a href="{{ url('/user/create') }}" class="button is-info is-fullwidth">
                            Registrar una nueva compra
                            </a><br><br>
                            @endcan
                        </h4>
                    </div>
                    <div class="card-body">

                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Name</th>
                                    <th width="40%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($roleIndex as $role)
                                <tr>
                                    <td>{{ $role->id }}</td>
                                    <td>{{ $role->name }}</td>
                                    <td>
                                    <a href="{{ route('role.edit', $role->id) }}" class="button is-primary">Editar Rol</a>

                                        @can('edit role')
                                        <a href="{{ route('role.edit', $role->id) }}" class="button is-primary">Editar Rol</a>
                                        @endcan

                                        @can('delete role')
                                        <form action="{{ route('role.destroy', $role->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <br><button type="submit" class="button is-danger">Eliminar user</button>
                                        </form>
                                        @endcan
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>