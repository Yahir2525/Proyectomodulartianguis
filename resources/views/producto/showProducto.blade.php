<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/producto/showIndex.css') }}"><!-- usamos el mismo CSS del index -->
    <title>Detalle del Producto</title>
    @php use App\Models\CarroProducto; @endphp
</head>
<body class="producto-index">
<div class="page-container">
<main class="content">
    @if (Auth::check())
        <br><x-barrageneral/>
    @else
        <br><x-barrasesion/>
    @endif

<section>
    <div>
        <br>
        <hr class="hr-grueso">
        <center><h1>Detalles del producto</h1></center>
        <hr class="hr-grueso"><br>

        <form action="{{ url('/producto/showProducto') }}" method="GET" class="buscar">
            <label for="busqueda">Buscar por ID o por nombre:</label>
            <input list="productos" id="busqueda" name="busqueda" placeholder="Ej. 21 o Cortina de baño" value="{{ request('busqueda') }}">
            <datalist id="productos">
                @foreach ($nombresUnicos as $nombre)
                    <option value="{{ $nombre }}">{{ $nombre }}</option>
                @endforeach
            </datalist>
            <input type="submit" value="Buscar">
        </form>

        @if($productos->isEmpty())
            <p>No se encontraron productos.</p>
        @else
            <form action="{{ url('/carro/agregar-multiples') }}" method="POST">
                @csrf

                @php $agrupadosPorTipo = $productos->groupBy('tipo'); @endphp

                @foreach($agrupadosPorTipo as $tipo => $grupo)
                    <h3>Tipo: {{ ucfirst($tipo) }}</h3>
                    <div class="table-responsive table-wrap">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Seleccionar</th>
                                    <th>ID producto</th>
                                    <th>Nombre</th>
                                    <th>Imagen</th>
                                    <th>Material</th>
                                    <th>Color</th>
                                    <th>Tamaño</th>
                                    <th>Precio</th>
                                    <th>Disponibles</th>
                                    <th>Cantidad</th>
                                    <th>Estado</th>
                                    <th>Editar</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($grupo as $producto)
                                    @php
                                        $reservadas = CarroProducto::where('id_producto', $producto->id_producto)->sum('cantidad');
                                        $disponibles = max(0, $producto->piezas - $reservadas);
                                        $esAdmin = Auth::check() && Auth::user()->hasRole('administrador');
                                    @endphp
                                    @if ($esAdmin || $producto->estado_producto)
                                        <tr class="{{ ($disponibles == 0 || $producto->estado_producto == 0) ? 'sin-stock' : '' }}">
                                            <td>
                                                <input type="checkbox" 
                                                    name="productos_seleccionados[]" 
                                                    value="{{ $producto->id_producto }}" 
                                                    {{ ($disponibles == 0 || $producto->estado_producto == 0) ? 'disabled' : '' }}>
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
                                            <td data-label="Cantidad">
                                                <input type="number" 
                                                    name="cantidades[{{ $producto->id_producto }}]" 
                                                    min="1" 
                                                    max="{{ $disponibles }}" 
                                                    class="cant-input"
                                                    {{ ($disponibles == 0 || $producto->estado_producto == 0) ? 'disabled' : '' }}>
                                            </td>
                                            <td>{{ $producto->estado_producto ? 'Activo' : 'Inactivo' }}</td>
                                            <td><a href="{{ route('producto.edit', $producto->id_producto) }}" class="btn btn-edit">Editar</a></td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach

                <!-- Paginación -->
                <div class="mt-4 d-flex justify-content-center align-items-center gap-3 flex-wrap">
                    {{ $productos->links('pagination::bootstrap-5') }}
                </div>

                {{-- ==== Parte inferior centrada ==== --}}
                <div class="form-footer">
                    @if(Auth::user()->hasRole('administrador'))
                        <label for="nombre_usuario"><strong>Buscar usuario:</strong></label>
                        <input list="usuarios" id="nombre_usuario" placeholder="Ej. Juan Pérez" autocomplete="off" class="form-input">
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
                                const option = Array.from(datalistUsuarios.options).find(o => o.value === inputUsuario.value);
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
                                        opcion.hidden = false;
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
                                opciones.forEach(opcion => { opcion.hidden = false; });
                                selectPedido.value = '';
                            }
                        </script>
                    @else
                        <input type="hidden" name="id_user" value="{{ Auth::id() }}">
                    @endif

                    <label for="id_pedido"><strong>Selecciona o crea un pedido:</strong></label>
                    <select name="id_pedido" id="id_pedido" required class="form-input">
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

                    <button type="submit" class="btn btn-agregar">Agregar seleccionados al carrito</button>
                </div>
            </form>
        @endif

        <div class="form-footer">
            <a href="{{ route('producto.index') }}" class="btn btn-gray">Volver al listado</a>
        </div>
    </div>
</section>
</main>
<x-footer/>
</div>
</body>
</html>
