<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <link rel="stylesheet" href="{{ asset('css/producto/productoIndex.css') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Principal de productos</title>
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
    </style>

    @php use App\Models\CarroProducto; @endphp
</head>
<body>
    <section>
        <div>
            <h1>Principal de productos</h1>
            <br>
            @if (Auth::check())
                <p>Sesión iniciada por: {{ Auth::user()->name }}</p>
            @else
                <p>No hay sesión activa.</p>
            @endif

            @can('create producto')
                <a href="{{ url('/producto/create') }}">Registrar un nuevo producto</a>
            @endcan
            <hr><br>

            <form action="{{ url('/producto/showProducto') }}" method="GET">
                <label for="busqueda">Buscar por ID o por nombre:</label>
                <input list="productos" id="busqueda" name="busqueda" placeholder="Ej. 21 o Cortina de baño" value="{{ request('busqueda') }}">

                <datalist id="productos">
                    @foreach ($productoIndex as $producto)
                        <option value="{{ $producto->id_producto }}">{{ $producto->nombre }}</option>
                        <option value="{{ $producto->nombre }}">{{ $producto->nombre }}</option>
                    @endforeach
                </datalist>

                <input type="submit" value="Buscar">
            </form>

            <br><br>

            <form action="{{ url('/carro/agregar-multiples') }}" method="POST">
                @csrf

                @if($productoIndex->isNotEmpty())
                    @php $agrupadosPorTipo = $productoIndex->groupBy('tipo'); @endphp
                    @foreach ($agrupadosPorTipo as $tipo => $productos)
                        <h3>Tipo: {{ ucfirst($tipo) }}</h3>
                        <table border="1" cellpadding="5" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Seleccionar</th>
                                    <th>ID producto</th>
                                    <th>Nombre</th>
                                    <th>Imagen</th>
                                    <th>Material</th>
                                    <th>Color</th>
                                    <th>Tamaño</th>
                                    <th>Precio unitario</th>
                                    <th>Piezas disponibles</th>
                                    <th>Cantidad</th>
                                    <th>Estado</th>
                                    <th>Editar</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach ($productos as $producto)
                                @if (Auth::user()->hasRole('administrador') || $producto->estado_producto)

                                @php
                                    $reservadas = CarroProducto::where('id_producto', $producto->id_producto)->sum('cantidad');
                                    $disponibles = max(0, $producto->piezas - $reservadas);
                                @endphp
                                <tr class="{{ $disponibles == 0 ? 'sin-stock' : '' }}">
                                    <td>
                                        <input type="checkbox" name="productos_seleccionados[]" value="{{ $producto->id_producto }}" {{ $disponibles == 0 ? 'disabled' : '' }}>
                                    </td>
                                    <td>{{ $producto->id_producto }}</td>
                                    <td>{{ $producto->nombre }}</td>
                                    <td>
                                        @if (!empty($producto->imagen_url))
                                            <img src="{{ $producto->imagen_url }}" alt="{{ $producto->nombre }}" style="max-width: 300px;">
                                        @else
                                            Sin imagen
                                        @endif
                                    </td>
                                    <td>{{ $producto->material }}</td>
                                    <td>{{ $producto->color }}</td>
                                    <td>{{ $producto->tamanio }}</td>
                                    <td>${{ number_format($producto->precio_unitario, 2) }}</td>
                                    <td class="{{ $disponibles == 0 ? 'resaltado' : '' }}">
                                        {{ $disponibles }}
                                    </td>
                                    <td>
                                        <input type="number" name="cantidades[{{ $producto->id_producto }}]" min="1" max="{{ $disponibles }}" class="cant-input" {{ $disponibles == 0 ? 'disabled' : '' }}>
                                    </td>
                                    <td>{{ $producto->estado_producto ? 'Activo' : 'Descontinuado' }}</td>
                                    <td><a href="{{ route('producto.edit', $producto->id_producto) }}">Editar</a></td>
                                </tr>
                                @endif
                            @endforeach
                            </tbody>
                        </table>
                        <br>
                    @endforeach

                    @if (Auth::user()->hasRole('administrador'))
                        <label for="nombre_usuario">Buscar usuario:</label>
                        <input list="usuarios" id="nombre_usuario" placeholder="Ej. Juan Pérez" autocomplete="off">
                        <input type="hidden" name="id_user" id="id_user">

                        <datalist id="usuarios">
                            @foreach ($usuarios as $usuario)
                                <option value="{{ $usuario->nombre_usuario }}" data-userid="{{ $usuario->id_user }}"></option>
                            @endforeach
                        </datalist>

                        <script>
                            const inputUsuario = document.getElementById('nombre_usuario');
                            const idUsuario = document.getElementById('id_user');
                            const datalistUsuarios = document.getElementById('usuarios');
                            const selectPedido = document.getElementById('id_pedido');

                            inputUsuario.addEventListener('input', () => {
                                const option = Array.from(datalistUsuarios.options).find(
                                    o => o.value === inputUsuario.value
                                );
                                if (option) {
                                    idUsuario.value = option.dataset.userid;
                                    filtrarPedidos(option.dataset.userid);
                                } else {
                                    idUsuario.value = '';
                                    mostrarTodosPedidos();
                                }
                            });

                            function filtrarPedidos(userId) {
                                const opciones = document.querySelectorAll('#id_pedido option');
                                opciones.forEach(opcion => {
                                    if (opcion.value === 'nuevo') {
                                        opcion.hidden = false; // siempre visible
                                    } else {
                                        opcion.hidden = opcion.dataset.user !== userId;
                                    }
                                });

                                if (selectPedido.value) {
                                    const opcionSeleccionada = selectPedido.querySelector(`option[value="${selectPedido.value}"]`);
                                    if (opcionSeleccionada && opcionSeleccionada.value !== 'nuevo' && opcionSeleccionada.dataset.user !== userId) {
                                        selectPedido.value = '';
                                    }
                                }
                            }
                            function mostrarTodosPedidos() {
                                const opciones = document.querySelectorAll('#id_pedido option');
                                opciones.forEach(opcion => {
                                    opcion.hidden = false;
                                });
                                selectPedido.value = '';
                            }
                        </script>
                    @else
                        <input type="hidden" name="id_user" value="{{ Auth::id() }}">
                    @endif
                    <br><br>
                    <label for="id_pedido">Selecciona o crea un pedido:</label>
                    <select name="id_pedido" id="id_pedido" required>
                        <option value="">-- Selecciona --</option>
                        <option value="nuevo">Crear nuevo pedido</option>
                        @foreach($pedidosUsuario as $pedido)
                            <option value="{{ $pedido->id_pedido }}"
                                    data-user="{{ $pedido->id_user }}"
                                    {{ $pedido->estado_pedido == 0 ? 'disabled' : '' }}>
                                Pedido #{{ $pedido->id_pedido }} - {{ $pedido->user->nombre_usuario ?? 'Usuario' }} {{ $pedido->estado_pedido == 0 ? '(CERRADO)' : '' }}
                            </option>
                        @endforeach
                    </select>

                    <br><br>

                    <button type="submit">Agregar seleccionados al carrito</button>
                @else
                    <p>No hay productos registrados.</p>
                @endif
            </form>
        </div>
    </section>
</body>
</html>
