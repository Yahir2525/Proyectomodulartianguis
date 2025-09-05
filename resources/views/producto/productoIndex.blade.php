<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/producto/productoIndex.css') }}">
    <title>Menú de productos</title>

    @php use App\Models\CarroProducto; @endphp
</head>
<body class="producto-index">
<div class="page-container">
<main class="content">
    @if (Auth::check())
        <br><x-barrageneral/>
    @else
        <br><x-barracreate/>
    @endif

<section>
    <div>
        <br>
        <hr class="hr-grueso">
        <center><h1>Menú de productos</h1></center>
        <hr class="hr-grueso"><br>

        @can('create producto')
            <a href="{{ url('/producto/create') }}" class="btn btn-registrar">Registrar un nuevo producto</a>
        @endcan
        <br>

        {{-- Buscador por ID o nombre --}}
        <form action="{{ url('/producto') }}" method="GET">
        <div class="buscar">
            <label for="buscar">Buscar por ID o por nombre:</label>
            <input list="productos" id="buscar" name="buscar"
                placeholder="Ej. 21 o Cortina de baño"
                value="{{ request('buscar') }}">
            <datalist id="productos">
                @foreach ($nombresUnicos as $nombre)
                    <option value="{{ $nombre }}">{{ $nombre }}</option>
                @endforeach
            </datalist>
            <input type="submit" value="Buscar">
        </div>

        {{-- ===== FILTROS ESPECIALES ===== --}}
        <div class="filtros">
            <div>
                <label for="filtro_tipo">Tipo:</label>
                <select name="tipo" id="filtro_tipo">
                    <option value="">-- Todos --</option>
                    @foreach ($tipos as $tipo)
                        <option value="{{ $tipo }}" {{ request('tipo') == $tipo ? 'selected' : '' }}>
                            {{ ucfirst($tipo) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="filtro_material">Material:</label>
                <select name="material" id="filtro_material">
                    <option value="">-- Todos --</option>
                    @foreach ($materiales as $material)
                        <option value="{{ $material }}" {{ request('material') == $material ? 'selected' : '' }}>
                            {{ $material }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="filtro_color">Color:</label>
                <select name="color" id="filtro_color">
                    <option value="">-- Todos --</option>
                    @foreach ($colores as $color)
                        <option value="{{ $color }}" {{ request('color') == $color ? 'selected' : '' }}>
                            {{ $color }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="filtro_tamanio">Tamaño:</label>
                <select name="tamanio" id="filtro_tamanio">
                    <option value="">-- Todos --</option>
                    @foreach ($tamanios as $tamanio)
                        <option value="{{ $tamanio }}" {{ request('tamanio') == $tamanio ? 'selected' : '' }}>
                            {{ $tamanio }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="precio_min">Precio mín:</label>
                <input type="number" name="precio_min" id="precio_min" value="{{ request('precio_min') }}" min="0" step="0.01">

                <label for="precio_max">Precio máx:</label>
                <input type="number" name="precio_max" id="precio_max" value="{{ request('precio_max') }}" min="0" step="0.01">
            </div>
            @can('create producto')
                <div>
                    <label for="estado">Estado:</label>
                    <select name="estado" id="estado">
                        <option value="">-- Todos --</option>
                        <option value="1" {{ request('estado') === '1' ? 'selected' : '' }}>Activo</option>
                        <option value="0" {{ request('estado') === '0' ? 'selected' : '' }}>Inactivo</option>
                    </select>
                </div>
            @endcan

            <button type="submit" class="btn btn-primary">Filtrar</button>
            <a href="{{ url('/producto') }}" class="btn btn-gray">Limpiar</a>
        </div>
        </form>

        @if($productoIndex->isNotEmpty())
            @php $agrupadosPorTipo = $productoIndex->groupBy('tipo'); @endphp

            @auth
                <form action="{{ url('/carro/agregar-multiples') }}" method="POST">
                    @csrf

                    @foreach ($agrupadosPorTipo as $tipo => $productos)
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
                                    @foreach ($productos as $producto)
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
                                                <td><a href="{{ route('producto.edit', $producto->id_producto) }}"  class="btn btn-edit">Editar</a></td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <br>
                    @endforeach

                    <!-- Links de paginación -->
                    <div class="mt-4 d-flex justify-content-center align-items-center gap-3 flex-wrap">
                        {{ $productoIndex->links('pagination::bootstrap-5') }}
                    </div>

                    {{-- ==== Parte inferior centrada ==== --}}
                    <div class="form-footer">
                        @if (Auth::user()->hasRole('administrador'))
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
                                @php
                                    $ocultar = false;

                                    if ($pedido->id_credito && $pedido->credito) {
                                        $ocultar = ($pedido->credito->estado == 0) || ($pedido->credito->fecha_vencimiento < now());
                                    }
                                @endphp

                                @if(!$ocultar)
                                    <option value="{{ $pedido->id_pedido }}"
                                            data-user="{{ $pedido->id_user }}"
                                            {{ $pedido->estado_pedido == 0 ? 'disabled' : '' }}>
                                        Pedido #{{ $pedido->id_pedido }} - {{ $pedido->user->nombre_usuario ?? 'Usuario' }} {{ $pedido->estado_pedido == 0 ? '(CERRADO)' : '' }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-agregar">Agregar seleccionados al carrito</button>
                    </div>
                </form>
            @endauth

            {{-- ===== Vista para VISITANTES (solo lectura) ===== --}}
            @guest
                <div class="solo-lectura">
                    <p class="nota">Estás en modo solo lectura. Inicia sesión para agregar productos al carrito.</p>

                    @foreach ($agrupadosPorTipo as $tipo => $productos)
                        <h3>Tipo: {{ ucfirst($tipo) }}</h3>
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID producto</th>
                                        <th>Nombre</th>
                                        <th>Imagen</th>
                                        <th>Material</th>
                                        <th>Color</th>
                                        <th>Tamaño</th>
                                        <th>Precio unitario</th>
                                        <th>Disponibles</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($productos as $producto)
                                        @php
                                            $reservadas = CarroProducto::where('id_producto', $producto->id_producto)->sum('cantidad');
                                            $disponibles = max(0, $producto->piezas - $reservadas);
                                        @endphp

                                        @if ($producto->estado_producto)
                                            <tr class="{{ ($disponibles == 0 || $producto->estado_producto == 0) ? 'sin-stock' : '' }}">
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
                                                <td>{{ $producto->estado_producto ? 'Activo' : 'Inactivo' }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <br>
                    @endforeach

                    <!-- Links de paginación -->
                    <div class="mt-4 d-flex justify-content-center">
                        {{ $productoIndex->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endguest
        @else
            <p>No hay productos registrados.</p>
        @endif
    </div>
</section>
</main>
<x-footer/>
</div>
</body>
</html>
