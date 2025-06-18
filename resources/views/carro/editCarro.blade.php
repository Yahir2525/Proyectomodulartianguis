<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Carro</title>
</head>
<body>
    <h1>Editar Carrito de Compras</h1>
    <hr>

    @if(session('error'))
        <p style="color: red;">{{ session('error') }}</p>
    @endif

    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    <form action="{{ route('carro.update', $carro->id_carro) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Producto -->
        <label for="id_producto">Producto:</label>
        <select name="id_producto" required>
            <option value="">Selecciona un producto</option>
            @foreach($productos as $producto)
                <option value="{{ $producto->id_producto }}"
                    {{ $producto->id_producto == $carro->id_producto ? 'selected' : '' }}>
                    {{ $producto->nombre }} - {{ $producto->piezas }} piezas disponibles
                </option>
            @endforeach
        </select>
        <br><br>

        <!-- Cantidad -->
        <label for="cantidad">Cantidad:</label>
        <input type="number" name="cantidad" min="1" value="{{ $carro->cantidad }}" required>
        <br><br>

        <!-- Pedido -->
        <label for="id_pedido">Pedido:</label>
        <select name="id_pedido" required>
            <option value="">Selecciona un pedido</option>
            @foreach($pedidosUsuario as $pedido)
                <option value="{{ $pedido->id_pedido }}"
                    {{ $pedido->id_pedido == $carro->id_pedido ? 'selected' : '' }}>
                    Pedido #{{ $pedido->id_pedido }}
                </option>
            @endforeach
        </select>
        <br><br>

        <button type="submit">Actualizar Carro</button>
    </form>

    <br>
    <a href="{{ route('carro.index') }}">Volver al Carrito</a>
</body>
</html>
