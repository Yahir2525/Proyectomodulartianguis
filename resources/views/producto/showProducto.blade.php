<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <link rel="stylesheet" href="{{ asset('css/producto/showProducto.css') }}">
    <title>Detalle del Producto</title>
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
    <h1>Búsqueda de productos</h1>

    {{-- Formularios de búsqueda --}}
    <form action="{{ url('/producto/showProducto') }}" method="GET">
        <label for="busqueda">Buscar por ID o nombre:</label>
        <input type="text" name="busqueda" id="busqueda" placeholder="Ej. 12 o cortina cocina" value="{{ request('busqueda') }}">
        <input type="submit" value="Buscar">
    </form>

    <hr>

    @if($productos->isEmpty())
        <p>No se encontraron productos.</p>
    @else
        <form action="{{ url('/carro/agregar-multiples') }}" method="POST">
            @csrf

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
                    @foreach($productos as $producto)
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
                                @if (!empty($producto->imagen)) 
                                    <img src="{{ Storage::disk('s3')->url($producto->imagen) }}" alt="Foto de producto" width="250">
                                @else
                                    <span>Sin imagen</span>
                                @endif
                            </td>
                            <td>{{ $producto->material }}</td>
                            <td>{{ $producto->color }}</td>
                            <td>{{ $producto->tamanio }}</td>
                            <td>${{ number_format($producto->precio_unitario, 2) }}</td>
                            <td class="{{ $disponibles == 0 ? 'resaltado' : '' }}">{{ $disponibles }}</td>
                            <td>
                                <input type="number" name="cantidades[{{ $producto->id_producto }}]" min="1" max="{{ $disponibles }}" class="cant-input" {{ $disponibles == 0 ? 'disabled' : '' }}>
                            </td>
                            <td>
                                {{ $producto->estado_producto ? 'Activo' : 'Descontinuado' }}
                            </td>

                            <td><a href="{{ route('producto.edit', $producto->id_producto) }}">Editar</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <br>

            @if(Auth::user()->hasRole('administrador'))
                <label for="nombre_usuario">Buscar usuario:</label>
                <input list="usuarios" id="nombre_usuario" placeholder="Ej. Juan Pérez">
                <input type="hidden" name="id_user" id="id_user">
                <datalist id="usuarios">
                    @foreach ($usuarios as $usuario)
                        <option value="{{ $usuario->nombre_usuario }}" data-userid="{{ $usuario->id_user }}"></option>
                    @endforeach
                </datalist>

                <script>
                    const inputUsuario = document.getElementById('nombre_usuario');
                    const idUsuario = document.getElementById('id_user');
                    const datalist = document.getElementById('usuarios');

                    inputUsuario.addEventListener('input', () => {
                        const option = Array.from(datalist.options).find(o => o.value === inputUsuario.value);
                        if (option) {
                            idUsuario.value = option.dataset.userid;
                            filtrarPedidos(option.dataset.userid);
                        }
                    });

                    function filtrarPedidos(userId) {
                        const opciones = document.querySelectorAll('#id_pedido option[data-user]');
                        opciones.forEach(opcion => {
                            opcion.hidden = opcion.dataset.user !== userId;
                        });
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
        </form>
    @endif

    <br><a href="{{ route('producto.index') }}">Volver al listado</a>
</body>
</html>
