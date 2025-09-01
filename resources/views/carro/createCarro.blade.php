<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/carro/createCarro.css') }}">
    <title>Crear Carro</title>

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

    function desactivarProductosSinStock() {
        const filas = document.querySelectorAll('table tbody tr');

        filas.forEach(fila => {
            const celdaDisp = fila.querySelector('td:nth-child(8)');
            if (!celdaDisp) return;

            const num = parseInt((celdaDisp.textContent || '0').replace(/[^\d-]/g, ''), 10);
            const disp = isNaN(num) ? 0 : num;

            const chk = fila.querySelector('td:first-child input[type="checkbox"]');
            const qty = fila.querySelector('td:last-child input[type="number"]');

            if (disp <= 0) {
                if (chk) { chk.checked = false; chk.disabled = true; }
                if (qty) { qty.value = ''; qty.disabled = true; }
                fila.classList.add('sin-stock');
            } else {
                if (chk) chk.disabled = false;
                if (qty) {
                    qty.disabled = false;
                    qty.min = '1';
                    qty.step = '1';
                    qty.max = String(disp);
                }
                fila.classList.remove('sin-stock');
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const inputNombre = document.getElementById('nombre_usuario');
        if (inputNombre) {
            inputNombre.addEventListener('input', filtrarPedidosPorUsuario);
            filtrarPedidosPorUsuario();
        }
        desactivarProductosSinStock();
    });
    </script>

</head>
<body>
<div class="page-container">
<main class="content">
<br><x-barracreate/>
    <a id="top"></a>
    <section>
    <hr class="hr-grueso"><center><h1>Crear nuevo carro</h1></center><hr class="hr-grueso"><br>

    @if(session('error'))
        <p style="color: red;">{{ session('error') }}</p>
    @endif

    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    {{-- Filtros de productos --}}
    <form action="{{ route('carro.create') }}" method="GET">
        <div class="buscar">
            <label for="buscar">Buscar producto:</label>
            <input list="productos" id="buscar" name="buscar"
                placeholder="Ej. mesa, cortina..." value="{{ request('buscar') }}">
            <datalist id="productos">
                @foreach ($nombresUnicos as $nombre)
                    <option value="{{ $nombre }}">{{ $nombre }}</option>
                @endforeach
            </datalist>
            <button type="submit" class="btn btn-agregar">Buscar</button>
        </div>

        <div class="filtros">
            <div>
                <label for="tipo">Tipo:</label>
                <select name="tipo" id="tipo" class="form-input">
                    <option value="">-- Todos --</option>
                    @foreach($tipos as $tipo)
                        <option value="{{ $tipo }}" {{ request('tipo') == $tipo ? 'selected' : '' }}>{{ $tipo }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="material">Material:</label>
                <select name="material" id="material" class="form-input">
                    <option value="">-- Todos --</option>
                    @foreach($materiales as $material)
                        <option value="{{ $material }}" {{ request('material') == $material ? 'selected' : '' }}>{{ $material }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="color">Color:</label>
                <select name="color" id="color" class="form-input">
                    <option value="">-- Todos --</option>
                    @foreach($colores as $color)
                        <option value="{{ $color }}" {{ request('color') == $color ? 'selected' : '' }}>{{ $color }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="tamanio">Tamaño:</label>
                <select name="tamanio" id="tamanio" class="form-input">
                    <option value="">-- Todos --</option>
                    @foreach($tamanios as $tamanio)
                        <option value="{{ $tamanio }}" {{ request('tamanio') == $tamanio ? 'selected' : '' }}>{{ $tamanio }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="precio_min">Precio mínimo:</label>
                <input type="number" name="precio_min" id="precio_min" value="{{ request('precio_min') }}" class="form-input">
            </div>
            <div>
                <label for="precio_max">Precio máximo:</label>
                <input type="number" name="precio_max" id="precio_max" value="{{ request('precio_max') }}" class="form-input">
            </div>
            @can('create producto')
            <div>
                <label for="estado">Estado:</label>
                <select name="estado" id="estado" class="form-input">
                    <option value="">-- Todos --</option>
                    <option value="1" {{ request('estado') === '1' ? 'selected' : '' }}>Activo</option>
                    <option value="0" {{ request('estado') === '0' ? 'selected' : '' }}>Inactivo</option>
                </select>
            </div>
            @endcan
            <div class="acciones">
                <button type="submit" class="btn btn-registrar">Filtrar</button>
                <a href="{{ route('carro.create') }}" class="btn btn-gray">Limpiar</a>
            </div>
        </div>
    </form>

    {{-- Lista de productos --}}
    <form action="{{ route('carro.agregarMultiples') }}" method="POST">
        @csrf
        <h3>Selecciona productos</h3><br>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Seleccionar</th>
                        <th>Nombre</th>
                        <th>Imagen</th>
                        <th>Material</th>
                        <th>Color</th>
                        <th>Tamaño</th>
                        <th>Precio</th>
                        <th>Disponibles</th>
                        <th>Cantidad</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($productos as $producto)
                        @if (Auth::user()->hasRole('administrador') || $producto->estado_producto)
                        <tr class="{{ $producto->piezas_disponibles == 0 ? 'sin-stock' : '' }}">
                            <td data-label="Seleccionar">
                                <input type="checkbox" name="productos_seleccionados[]" value="{{ $producto->id_producto }}">
                            </td>
                            <td>{{ $producto->nombre }}</td>
                            <td data-label="Imagen">
                                @if (!empty($producto->imagen)) 
                                    <img src="{{ Storage::disk('s3')->url($producto->imagen) }}" alt="Foto de producto" width="250">
                                @else
                                    <span>Sin imagen</span>
                                @endif
                            </td>
                            <td data-label="Material">{{ $producto->material }}</td>
                            <td data-label="Color">{{ $producto->color }}</td>
                            <td data-label="Tamaño">{{ $producto->tamanio }}</td>
                            <td data-label="Precio">${{ number_format($producto->precio_unitario, 2) }}</td>
                            @php $disp = max(0, (int)($producto->piezas_disponibles ?? 0)); @endphp
                            <td data-label="Disponibles">{{ $disp }}</td>
                            <td data-label="Cantidad">
                                <input type="number" name="cantidades[{{ $producto->id_producto }}]" class="cant-input form-input"
                                    min="1" step="1" max="{{ $disp }}" {{ $disp === 0 ? 'disabled' : '' }} />
                            </td>
                        </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Links de paginación --}}
        <div class="mt-4 d-flex justify-content-center">
            {{ $productos->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>

        <center>
        <br>
        {{-- Buscador de usuario para admin --}}
        @if(Auth::user()->hasRole('administrador'))
            <label for="nombre_usuario">Buscar usuario:</label>
            <input list="usuarios" id="nombre_usuario" class="form-input" placeholder="Ej. Juan Pérez" required>
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
        <select name="id_pedido" id="id_pedido" class="form-input">
            <option value="" disabled selected>-- Ninguno --</option>
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
        <button type="submit" class="btn btn-registrar">Agregar productos seleccionados</button>
    </form>

    <br><br>
    <a href="/carro" class="btn btn-danger">Cancelar</a>
    <a href="#top" class="btn btn-agregar" aria-label="Ir arriba">Ir arriba</a>
    </center>
</section>
</main>
<x-footer/>
</div>
</body>
</html>
