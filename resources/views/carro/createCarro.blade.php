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
                <option value="{{ $producto->id_producto }}"
                    {{ isset($carro) && $producto->id_producto == $carro->id_producto ? 'selected' : '' }}>
                    {{ $producto->nombre }} - {{ $producto->piezas_disponibles }} piezas disponibles
                </option>
            @endforeach
        </select>
        <br><br>
        
        <!-- Cantidad -->
        <label for="cantidad">Cantidad:</label>
        <input type="number" name="cantidad" min="1" required>
        <br><br>
        @if($pedidosUsuario->isNotEmpty())
            <!-- Seleccionar pedido existente -->
            <label for="id_pedido">Selecciona un pedido existente:</label>
            <select name="id_pedido">
                <option value="">-- Ninguno --</option>
                @foreach($pedidosUsuario as $pedido)
                    <option value="{{ $pedido->id_pedido }}"
                        {{ session('pedido_reciente') == $pedido->id_pedido ? 'selected' : '' }}>
                        Pedido #{{ $pedido->id_pedido }}
                    </option>
                @endforeach
            </select>
            <br><br>
        @else
            <!-- Casilla para solicitar uno nuevo -->
            <label>
                <input type="checkbox" name="nuevo_pedido" value="1">
                Crear un nuevo pedido
            </label>
            <br><br>
        @endif
        <button type="submit">Agregar al carrito</button>
    </form>

    <br>
    <a href="/">Inicio</a> |
    <a href="/carro">Ver carrito</a>
</body>
</html>
