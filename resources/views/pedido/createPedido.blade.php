<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Pedido</title>
</head>
<body>
    <h1>Crear nuevo pedido</h1>

    <form action="{{ url('/pedido') }}" method="POST">
        @csrf

        <input type="hidden" name="id_user" value="{{ Auth::id() }}">

        <button type="submit">Crear pedido</button>
    </form>

    <br>
    <a href="{{ url('/') }}">Inicio</a> |
    <a href="{{ url('/pedido') }}">Regresar</a>
</body>
</html>
