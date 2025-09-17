<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/user/userIndex.css') }}">
    <link rel="icon" href="{{ asset('img/blanco.ico') }}" type="image/x-icon">
    <title>Detalles del usuario</title>
</head>
<body>
    <div class="page-container">
        <main class="content">
        <br><x-barraadmin/>
            <section class="container">
                <br><hr class="hr-grueso"><center><h1>Detalles del usuario</h1></center><hr class="hr-grueso"><br>

                <form action="{{ url('/user/showUser') }}" method="GET">
                    <label for="buscar">Buscar usuario:</label>
                    <input 
                        type="text" 
                        id="buscar" 
                        name="busqueda" 
                        placeholder="Ej. 21 o Pepito" 
                        list="sugerencias"
                        value="{{ request('busqueda') }}"
                        autocomplete="off"
                    >
                    <datalist id="sugerencias">
                        @foreach (\App\Models\User::select('nombre_usuario')->distinct()->get() as $usuario)
                            <option value="{{ $usuario->nombre_usuario }}"></option>
                        @endforeach
                    </datalist>
                    <input type="submit" value="Buscar">
                </form>

                @if (session('error'))
                    <p style="color: red;">{{ session('error') }}</p>
                @endif

                @if (isset($usuarios) && $usuarios->count())

                    @php
                        $administradores = $usuarios->filter(fn($u) => $u->hasRole('administrador'));
                        $usuariosNormales = $usuarios->filter(fn($u) => !$u->hasRole('administrador'));
                        $niveles = ['excelente', 'bueno', 'malo'];
                    @endphp

                    @foreach (['Administradores' => $administradores, 'Usuarios normales' => $usuariosNormales] as $tituloRol => $grupo)
                        @if ($grupo->isNotEmpty())
                            <h2>{{ $tituloRol }}</h2>

                            @foreach ($niveles as $nivel)
                                @php
                                    $usuariosNivel = $grupo->filter(fn($u) => strtolower($u->nivel_usuario) === $nivel);
                                @endphp

                                @if($usuariosNivel->isNotEmpty())
                                    <h3>Nivel {{ ucfirst($nivel) }}</h3>
                                    <div class="table-wrap">
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Imagen</th>
                                                    <th>Nombre</th>
                                                    <th>Correo</th>
                                                    <th>Usuario</th>
                                                    <th>Teléfono</th>
                                                    <th>Dirección</th>
                                                    <th>Editar</th>
                                                    <th>Eliminar</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($usuariosNivel as $user)
                                                    <tr>
                                                        <td data-label="ID">{{ $user->id_user }}</td>
                                                        <td data-label="Imagen">
                                                            @if (!empty($user->imagen))
                                                                <img src="{{ Storage::disk('s3')->url($user->imagen) }}" alt="Foto de perfil" width="70">
                                                            @else
                                                                <span>Sin imagen</span>
                                                            @endif
                                                        </td>
                                                        <td data-label="Nombre">{{ $user->name }}</td>
                                                        <td data-label="Correo">{{ $user->email }}</td>
                                                        <td data-label="Usuario">{{ $user->nombre_usuario }}</td>
                                                        <td data-label="Teléfono">{{ $user->telefono ?? 'No registrado' }}</td>
                                                        <td data-label="Dirección">{{ $user->direccion ?? 'No registrada' }}</td>
                                                        <td data-label="Editar">
                                                            @can('edit user')
                                                                <a class="btn btn-edit" href="{{ route('user.edit', $user->id_user) }}">
                                                                    Editar
                                                                </a>
                                                            @endcan
                                                        </td>
                                                        <td data-label="Eliminar">
                                                            @can('delete user')
                                                                <form action="{{ route('user.destroy', $user->id_user) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este usuario?');">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-danger">Eliminar</button>
                                                                </form>
                                                            @endcan
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                        <center><br><br><a href="{{ route('abono.index') }}" class="btn btn-secondary fw-bold">Volver al listado</a></center>
                @endif
            </section>
        </main>
        <x-footer/>
    </div>
</body>
</html>
