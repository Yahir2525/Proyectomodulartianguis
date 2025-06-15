<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Carro</title>
</head>
<body>
    <h1>Crear carrito de compras</h1>
    <hr>

    @if(session('error'))
        <p style="color: red;">{{ session('error') }}</p>
    @endif

    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    <form action="{{ url('/carro') }}" method="POST">
        @csrf

        <!-- Usuario logueado -->
        <input type="hidden" name="id_user" value="{{ $usuarioId }}">

        <!-- Producto a agregar -->
        <label for="id_producto">Producto:</label>
        <select name="id_producto" required>
            <option value="">Selecciona un producto</option>
            @foreach($productos as $producto)
                <option value="{{ $producto->id_producto }}">
                    {{ $producto->nombre }} - {{ $producto->piezas }} piezas disponibles
                </option>
            @endforeach
        </select>
        <br><br>

        <!-- Pedido (puedes eliminar esto si no lo usas aún) -->
        <label for="id_pedido">ID del pedido (si aplica):</label>
        <input type="number" name="id_pedido">
        <br><br>

        <button type="submit">Agregar al carrito</button>
    </form>

    <br>
    <a href="/">Inicio</a> |
    <a href="/carro">Ver carrito</a>
</body>
</html>
