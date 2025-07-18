<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
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

    <form action="{{ route('carro.update', ['carro' => $carro->id_carro, 'id_producto' => $productoActual->id_producto]) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Producto fijo, no editable -->
        <!-- Producto -->
        <label for="id_producto">Producto:</label>
        <select name="id_producto" required>
            <option value="">Selecciona un producto</option>
            @foreach($productos as $producto)
                <option value="{{ $producto->id_producto }}"
                    {{ $producto->id_producto == $productoActual->id_producto ? 'selected' : '' }}>
                    {{ $producto->nombre }} - {{ $producto->piezas_disponibles }} piezas disponibles
                </option>
            @endforeach
        </select>
        <br><br>

        <!-- Cantidad -->
        <label for="cantidad">Cantidad:</label>
        <input type="number" name="cantidad" min="1" value="{{ $cantidad }}" required>


        <label for="id_pedido">Selecciona un pedido existente (opcional):</label>
        <select name="id_pedido">
            <option value="">-- Ninguno --</option>
            @foreach($pedidosUsuario as $pedido)
                <option value="{{ $pedido->id_pedido }}"
                    {{ $carro->id_pedido == $pedido->id_pedido ? 'selected' : '' }}
                    {{ $pedido->estado_pedido == 0 ? 'disabled' : '' }}>
                    Pedido #{{ $pedido->id_pedido }}{{ $pedido->estado_pedido == 0 ? ' (cerrado)' : '' }}
                </option>
            @endforeach
        </select>
        <br><br>

        <!-- Casilla para crear nuevo pedido -->
        <label>
            <input type="checkbox" name="nuevo_pedido" value="1">
            Crear un nuevo pedido
        </label>



        <button type="submit">Actualizar Carro</button>
    </form>

    <br>
    <a href="{{ route('carro.index') }}">Volver al Carrito</a>
</body>
</html>
