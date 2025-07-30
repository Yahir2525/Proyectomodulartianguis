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
            const nombre = document.getElementById('nombre_usuario').value;
            const datalist = document.getElementById('usuarios');
            const hiddenInput = document.getElementById('id_user');
            const opciones = datalist.options;

            let idEncontrado = null;
            for (let i = 0; i < opciones.length; i++) {
                if (opciones[i].value === nombre) {
                    idEncontrado = opciones[i].dataset.userid;
                    break;
                }
            }

            if (idEncontrado) {
                hiddenInput.value = idEncontrado;

                const opcionesPedidos = document.querySelectorAll('#id_pedido option[data-user]');
                opcionesPedidos.forEach(op => {
                    op.style.display = (op.dataset.user == idEncontrado) ? 'block' : 'none';
                });

                document.getElementById('id_pedido').value = '';
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const inputNombre = document.getElementById('nombre_usuario');
            if (inputNombre) {
                inputNombre.addEventListener('input', filtrarPedidosPorUsuario);
                filtrarPedidosPorUsuario();
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

        {{-- Tabla de productos --}}
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
                    <th>Cantidad</th>
                </tr>
            </thead>
            <tbody>
                @foreach($productos as $producto)
                    @if (Auth::user()->hasRole('administrador') || $producto->estado_producto)

                    <tr class="{{ $producto->piezas_disponibles == 0 ? 'sin-stock' : '' }}">
                        <td>
                            <input type="checkbox" name="productos_seleccionados[]" value="{{ $producto->id_producto }}">
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
                        <td>{{ $producto->piezas_disponibles }}</td>
                        <td>
                            <input type="number" name="cantidades[{{ $producto->id_producto }}]" max="{{ $producto->piezas_disponibles }}" class="cant-input">
                        </td>
                    </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
        <br>
        {{-- Buscador de usuario para admin --}}
        @if(Auth::user()->hasRole('administrador'))
            <label for="nombre_usuario">Buscar usuario:</label>
            <input list="usuarios" id="nombre_usuario" placeholder="Ej. Juan Pérez" required>
            <input type="hidden" name="id_user" id="id_user">
            <datalist id="usuarios">
                @foreach ($usuarios as $usuario)
                    <option value="{{ $usuario->nombre_usuario }}" data-userid="{{ $usuario->id_user }}"></option>
                @endforeach
            </datalist>
        @else
            <input type="hidden" name="id_user" value="{{ $usuario->id_user }}">
        @endif

        <br><br>

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
            <option value="nuevo">Crear nuevo pedido</option>
        </select>

        <br><br>

        <button type="submit">Agregar productos seleccionados</button>
    </form>

    <br>
    <a href="/">Inicio</a> |
    <a href="/carro">Ver carrito</a>
</body>
</html>
