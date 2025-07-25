<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Carro</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .sin-stock { background-color: #ffe5e5; }
        .resaltado { font-weight: bold; color: red; }
        .cant-input { width: 60px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { padding: 6px; border: 1px solid #999; text-align: center; }
    </style>

    <script>
        function filtrarPedidosPorUsuario() {
            const usuarioId = document.getElementById('usuario_selector').value;
            const opciones = document.querySelectorAll('#id_pedido option');

            opciones.forEach(op => {
                if (!op.dataset.user) return;

                op.style.display = (op.dataset.user == usuarioId) ? 'block' : 'none';
            });

            document.getElementById('id_pedido').value = '';
        }

        document.addEventListener('DOMContentLoaded', () => {
            const selectorUsuario = document.getElementById('usuario_selector');
            if (selectorUsuario) {
                selectorUsuario.addEventListener('change', filtrarPedidosPorUsuario);
                filtrarPedidosPorUsuario(); // Inicializar
            }
        });
    </script>
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

    <form action="{{ route('carro.agregarMultiples') }}" method="POST">
        @csrf

        @if(Auth::user()->hasRole('administrador'))
            <label for="usuario_selector">Seleccionar Usuario:</label>
            <select id="usuario_selector" name="id_user" required>
                <option value="">-- Selecciona un usuario --</option>
                @foreach($usuarios as $u)
                    <option value="{{ $u->id_user }}">{{ $u->nombre_usuario }}</option>
                @endforeach
            </select>
        @else
            <input type="hidden" name="id_user" value="{{ $usuario->id_user }}">
        @endif

        <br><br>

        <h3>Selecciona productos</h3>
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
                    <th>Cantidad</th> <!-- Nueva columna -->
                </tr>
            </thead>
            <tbody>
                @foreach($productos as $producto)
                    <tr class="{{ $producto->piezas_disponibles == 0 ? 'sin-stock' : '' }}">
                        <td>
                            <input type="checkbox" name="productos_seleccionados[]" value="{{ $producto->id_producto }}"
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
                        <td>
                            <input type="number" name="cantidades[{{ $producto->id_producto }}]" max="{{ $producto->piezas_disponibles }}" {{ $producto->piezas_disponibles == 0 ? 'disabled' : '' }} class="cant-input">
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <br>

        <label for="id_pedido">Selecciona un pedido existente (opcional):</label>
        <select name="id_pedido" id="id_pedido">
            <option value="">-- Ninguno --</option>
            @foreach($pedidos as $pedido)
                <option value="{{ $pedido->id_pedido }}"
                        data-user="{{ $pedido->id_user }}"
                        {{ session('pedido_reciente') == $pedido->id_pedido ? 'selected' : '' }}
                        {{ $pedido->estado_pedido == 0 ? 'disabled' : '' }}>
                    Pedido #{{ $pedido->id_pedido }}{{ $pedido->estado_pedido == 0 ? ' (cerrado)' : '' }}
                </option>
            @endforeach
            <option value="nuevo">Crear nuevo pedido</option> <!-- Opción para nuevo pedido -->
        </select>

        <br><br>

        <button type="submit">Agregar productos seleccionados</button>
    </form>


    <br>
    <a href="/">Inicio</a> |
    <a href="/carro">Ver carrito</a>
</body>
</html>
