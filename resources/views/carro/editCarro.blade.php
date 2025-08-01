<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Editar Carro</title>
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
    <h1>Editar Carrito de Compras</h1>
    <hr>

    @php
        $pedidoCerrado = $carro->pedido && $carro->pedido->estado_pedido == 0;
        $usuarioBloqueado = $carro->user && $carro->user->tienePagosAtrasadosSinAbonar();
        $descontinuado = !$productoActual->estado_producto;
    @endphp

    @if(session('error'))
        <p style="color: red;">{{ session('error') }}</p>
    @endif

    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    @if($pedidoCerrado)
        <p style="color:red;"><strong>Este pedido ya está cerrado. No puedes editar el carro.</strong></p>
    @elseif($usuarioBloqueado)
        <p style="color:red;"><strong>No puedes editar este carro porque tienes pagos vencidos sin abonar. Tu acceso a crédito está bloqueado.</strong></p>
    @else
        <form action="{{ route('carro.update', ['carro' => $carro->id_carro, 'id_producto' => $productoActual->id_producto]) }}" method="POST">
            @csrf
            @method('PUT')

            <h3>Selecciona el producto</h3>
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
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($productos as $producto)
                        @php
                            $esActual = $producto->id_producto == $productoActual->id_producto;
                            $sinStock = $producto->piezas_disponibles == 0;
                            $desactivado = !$producto->estado_producto;
                            $deshabilitado = (!$esActual && ($sinStock || $desactivado));
                        @endphp
                        <tr class="{{ $sinStock || $desactivado ? 'sin-stock' : '' }}">
                            <td>
                                <input type="radio"
                                    name="id_producto"
                                    value="{{ $producto->id_producto }}"
                                    {{ $esActual ? 'checked' : '' }}
                                    {{ $deshabilitado ? 'disabled' : '' }}>
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
                            <td class="{{ $sinStock ? 'resaltado' : '' }}">
                                {{ $producto->piezas_disponibles }}
                            </td>
                            <td>{{ $producto->estado_producto ? 'Activo' : 'Descontinuado' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <br>

            <label for="cantidad">Cantidad:</label>
            <input type="number"
                name="cantidad"
                id="cantidad"
                min="1"
                max="{{ $descontinuado ? $cantidad : $productoActual->piezas_disponibles }}"
                value="{{ $cantidad }}"
                required
                class="cant-input"
            >
            @if($descontinuado)
                <p style="color: red;">
                    Este producto está descontinuado. Solo puedes reducir la cantidad actual (máximo {{ $cantidad }}).
                </p>
            @endif

            <br><br>

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

            <label>
                <input type="checkbox" name="nuevo_pedido" value="1">
                Crear un nuevo pedido
            </label>

            <br><br>

            <button type="submit">Actualizar Carro</button>
        </form>
    @endif

    <br>
    <a href="{{ route('carro.index') }}">Volver al Carrito</a>
</body>
</html>
