<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Crear Pedido</title>
</head>
<body>
    <h1>Crear nuevo pedido</h1>

    <form action="{{ url('/pedido') }}" method="POST">
        @csrf

        @if ($usuarios) 
            <label for="id_user">Selecciona el usuario para este pedido:</label>
            <select name="id_user" id="id_user" required>
                <option value="">-- Selecciona un usuario --</option>
                @foreach ($usuarios as $user)
                    <option value="{{ $user->id_user }}">{{ $user->nombre_usuario }}</option>
                @endforeach
            </select>
        @else
            <input type="hidden" name="id_user" value="{{ $usuario->id_user }}">
        @endif

        <br><br>
        <button type="submit">Crear pedido</button>
    </form>

    <br>
    <a href="{{ url('/') }}">Inicio</a> |
    <a href="{{ url('/pedido') }}">Regresar</a>
</body>
</html>
