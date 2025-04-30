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

            @can('create user')
                <a href="{{ url('/user/create') }}" class="btn btn-primary float-end">Añadir un usuario</a>
            @endcan
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
                        @can('edit user')
                        <a href="{{ url('user.edit', $user->id_user) }}" class="btn btn-success">Edit</a>
                        @endcan
                        @can('delete user')
                        <form action="{{ url('/user', $user->id_user) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <br><button type="submit" class="button is-danger">Eliminar Pedido</button>
                        </form>
                        @endcan
                    </center>
                @endforeach 
            @endif
        </div>
    </section>

    <section>

    <!-- <div class="container mt-5">
        <a href="{{ url('/role') }}" class="btn btn-primary mx-1">Roles</a>
        <a href="{{ url('/permission') }}" class="btn btn-info mx-1">Permissions</a>
        <a href="{{ url('/user') }}" class="btn btn-warning mx-1">Users</a>
    </div>
    
    <form action="{{ url('/user/showUser') }}" method="GET"> 
                <div class="sub">
                    <label for="id">ID de user a buscar:</label>
                    <input type="text" id="id" name="id_user" placeholder="21" autofocus>
                </div><br><br>
                <input type="submit" id="enviar" name="enviar" value="buscar">
            </form>

    <div class="container mt-2">
        <div class="row">
            <div class="col-md-12">
                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif

                <div class="card mt-3">
                    <div class="card-header">
                        <h4>Users
                            @can('create user')
                            <a href="{{ url('/user/create') }}" class="btn btn-primary float-end">Add User</a>
                            @endcan
                        </h4>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Genero</th>
                                    <th>Telefono</th>
                                    <th>Direccion</th>
                                    <th>Nombre_usuario</th>
                                    <th>Creacion</th>
                                    <th>Actualización</th>
                                    <th>Roles</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($userIndex as $user)
                                <tr>
                                    <td>{{ $user->id_user }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td> 
                                    <td>{{ $user->genero }}</td>
                                    <td>{{ $user->edad }}</td>
                                    <td>{{ $user->telefono }}</td>
                                    <td>{{ $user->direccion }}</td>
                                    <td>{{ $user->nombre_usuario }}</td>
                                    <td>{{ $user->created_at }}</td>
                                    <td>{{ $user->updated_at }}</td>
                                </tr>
                                <td>
                                    @if (!empty($user->getRoleNames()))
                                        @foreach ($user->getRoleNames() as $rolename)
                                            <label class="badge bg-primary mx-1">{{ $rolename }}</label>
                                        @endforeach
                                    @endif
                                </td>
                                <td>
                                    @can('edit user')
                                    <a href="{{ url('user.edit', $user->id_user) }}" class="btn btn-success">Edit</a>
                                    @endcan
                                    @can('delete user')
                                    <form action="{{ url('/user', $user->id_user) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <br><button type="submit" class="button is-danger">Eliminar Pedido</button>
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

    </section> -->


</body>
</html>