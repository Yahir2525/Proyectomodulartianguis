<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="{{ asset('css/carro/editCarro.css') }}">
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

        /* --------- Mejora de responsividad (solo CSS, sin tocar HTML) --------- */
        * { box-sizing: border-box; }
        html { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; }
        body { margin: 0; padding: 16px; color: #222; background: #fff; }
        h1 { margin: 0 0 12px; font-size: clamp(1.25rem, 1rem + 1.2vw, 2rem); }

        /* Imágenes dentro de la tabla: evitan desbordar en móvil */
        td img {
            width: auto;
            max-width: min(220px, 100%);
            height: auto;
            display: block;
            margin: 0 auto;
        }

        /* Inputs/botones más cómodos */
        input, select, button { line-height: 1.2; }
        button { padding: 8px 12px; cursor: pointer; }

        @media (max-width: 992px) {
            body { padding: 12px; }
        }

        /* En móvil: tabla desplazable horizontalmente */
        @media (max-width: 768px) {
            table {
                display: block;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                border: 1px solid #e9e9e9;
            }
            thead, tbody, tr, th, td { white-space: nowrap; }

            /* Controles del formulario a ancho completo */
            select, .cant-input, button {
                width: 100%;
                max-width: 100%;
                margin: 6px 0;
            }

            /* La cantidad en la tabla quepa bien en celdas estrechas */
            .cant-input { min-width: 80px; }
        }

        /* Móviles muy pequeños: permitir saltos de línea en celdas y toque cómodo */
        @media (max-width: 480px) {
            thead, tbody, tr, th, td { white-space: normal; }
            th, td { padding: 6px; }
            button, input[type="number"], select { min-height: 44px; }
        }
        /* --------------------------------------------------------------------- */
    </style>
</head>
<body>
    <br><hr class="hr-grueso"><center><h1>Editar carro</h1></center><hr class="hr-grueso">
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
                                @if (!empty($producto->imagen_url))
                                    <img src="{{ $producto->imagen_url }}" alt="{{ $producto->nombre }}" width="250" loading="lazy">
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
