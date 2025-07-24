<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Carro</title>
    <style>
        .sin-stock {
            background-color: #ffe5e5;
        }
        .resaltado {
            font-weight: bold;
            color: red;
        }
        .cant-input {
            width: 60px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            padding: 6px;
            border: 1px solid #999;
            text-align: center;
        }
    </style>
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

        <input type="hidden" name="id_user" value="{{ $usuario->id_user }}">

        <h3>Selecciona un producto</h3>
        <table>
            <thead>
                <tr>
                    <th>Seleccionar</th>
                    <th>Imagen</th>
                    <th>Nombre</th>
                    <th>Material</th>
                    <th>Color</th>
                    <th>Tamaño</th>
                    <th>Precio</th>
                    <th>Piezas disponibles</th>
                </tr>
            </thead>
            <tbody>
                @foreach($productos as $producto)
                    <tr class="{{ $producto->piezas_disponibles == 0 ? 'sin-stock' : '' }}">
                        <td>
                            <input type="radio" name="id_producto" value="{{ $producto->id_producto }}"
                                {{ old('id_producto') == $producto->id_producto ? 'checked' : '' }}
                                {{ $producto->piezas_disponibles == 0 ? 'disabled' : '' }}>
                        </td>
                        <td>
                            @if($producto->imagen)
                                <img src="{{ asset($producto->imagen) }}" alt="imagen" width="100">
                            @else
                                Sin imagen
                            @endif
                        </td>
                        <td>{{ $producto->nombre }}</td>
                        <td>{{ $producto->material }}</td>
                        <td>{{ $producto->color }}</td>
                        <td>{{ $producto->tamanio }}</td>
                        <td>${{ number_format($producto->precio_unitario, 2) }}</td>
                        <td class="{{ $producto->piezas_disponibles == 0 ? 'resaltado' : '' }}">
                            {{ $producto->piezas_disponibles }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <br>
        <label for="cantidad">Cantidad:</label>
        <input type="number" name="cantidad" min="1" required value="{{ old('cantidad') }}">
        <br><br>

        <label for="id_pedido">Selecciona un pedido existente (opcional):</label>
        <select name="id_pedido">
            <option value="">-- Ninguno --</option>
            @foreach($pedidosUsuario as $pedido)
                <option value="{{ $pedido->id_pedido }}"
                    {{ session('pedido_reciente') == $pedido->id_pedido ? 'selected' : '' }}
                    {{ $pedido->estado_pedido == 0 ? 'disabled' : '' }}>
                    Pedido #{{ $pedido->id_pedido }}{{ $pedido->estado_pedido == 0 ? ' (cerrado)' : '' }}
                </option>
            @endforeach
        </select>
        <br><br>

        <label>
            <input type="checkbox" name="nuevo_pedido" value="1">
            Crear un nuevo pedido
        </label>
        <br><br>

        <button type="submit">Agregar al carrito</button>
    </form>

    <br>
    <a href="/">Inicio</a> |
    <a href="/carro">Ver carrito</a>
</body>
</html>
