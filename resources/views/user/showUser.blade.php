<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <link rel="stylesheet" href="{{ asset('css/user/showUser.css') }}">
    <title>Usuarios encontrados</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        h2 { margin-bottom: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        img { max-width: 100px; border-radius: 4px; }
        .acciones a, .acciones form { display: inline-block; margin-right: 5px; }
    </style>
</head>
<body>

<h2>Buscar usuario</h2>

<form action="{{ url('/user/showUser') }}" method="GET">
    <label for="busqueda">Buscar por ID o nombre de usuario:</label>
    <input type="text" id="busqueda" name="busqueda" placeholder="Ej. 21 o pepito"
        list="sugerencias" value="{{ request('busqueda') }}">
    
    <datalist id="sugerencias">
        @foreach (\App\Models\User::select('nombre_usuario')->distinct()->get() as $usuario)
            <option value="{{ $usuario->nombre_usuario }}"></option>
        @endforeach
    </datalist>

    <button type="submit">Buscar</button>
</form>

@if (session('error'))
    <p style="color: red;">{{ session('error') }}</p>
@endif

@if (isset($usuarios) && $usuarios->count())
    <h3>Resultados ({{ $usuarios->count() }})</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Imagen</th>
                <th>Correo</th>
                <th>Nombre de usuario</th>
                <th>Teléfono</th>
                <th>Dirección</th>
                <th>Nivel de usuario</th>
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
                        @if (!empty($user->imagen)) {{-- ruta relativa en BD, p.ej. "perfiles/archivo.jpg" --}}
                            <img src="{{ Storage::disk('s3')->url($user->imagen) }}" alt="Foto de perfil" width="250">
                        @else
                            <span>Sin imagen</span>
                        @endif
                    </td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->nombre_usuario }}</td>
                    <td>{{ $user->telefono ?? 'No registrado' }}</td>
                    <td>{{ $user->direccion ?? 'No registrada' }}</td>
                    <td>{{ $user->nivel_usuario}}</td>
                    <td>{{ $user->created_at }}</td>
                    <td>{{ $user->updated_at }}</td>
                    <td class="acciones">
                        <a href="{{ route('user.edit', $user->id_user) }}">Editar</a>

                        <form action="{{ route('user.destroy', $user->id_user) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este usuario?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="color: red;">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

</body>
</html>
