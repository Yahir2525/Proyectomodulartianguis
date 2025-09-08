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
            const disp = parseInt(fila.dataset.disp || "0", 10);
            const estado = parseInt(fila.dataset.estado || "0", 10);

            const chk = fila.querySelector('td:first-child input[type="checkbox"]');
            const qty = fila.querySelector('td:last-child input[type="number"]');

            if (disp <= 0 || estado === 0) {
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
    <form id="form-filtros" action="{{ route('carro.create') }}" method="GET">
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
            <!-- @can('create producto')
            <div>
                <label for="estado">Estado:</label>
                <select name="estado" id="estado" class="form-input">
                    <option value="">-- Todos --</option>
                    <option value="1" {{ request('estado') === '1' ? 'selected' : '' }}>Activo</option>
                    <option value="0" {{ request('estado') === '0' ? 'selected' : '' }}>Inactivo</option>
                </select>
            </div>
            @endcan -->
            <div class="acciones">
                <button type="submit" class="btn btn-registrar">Filtrar</button>
                <a id="btn-limpiar" href="{{ route('carro.create') }}" class="btn btn-gray">Limpiar</a>
            </div>
        </div>
    </form>

    {{-- Lista de productos --}}
    <form id="form-seleccion" action="{{ route('carro.agregarMultiples') }}" method="POST">
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
                        <tr class="{{ ($producto->piezas_disponibles == 0 || !$producto->estado_producto) ? 'sin-stock' : '' }}"
                            data-disp="{{ (int)($producto->piezas_disponibles ?? 0) }}"
                            data-estado="{{ $producto->estado_producto }}"
                            data-id="{{ $producto->id_producto }}" >

                            <td data-label="Seleccionar">
                                <input type="checkbox"
                                    class="chk-producto"
                                    data-id="{{ $producto->id_producto }}"  {{-- NUEVO --}}
                                    name="productos_seleccionados[]"
                                    value="{{ $producto->id_producto }}"
                                    {{ ($producto->piezas_disponibles == 0 || !$producto->estado_producto) ? 'disabled' : '' }}
                                    {{ array_key_exists($producto->id_producto, $seleccion ?? []) ? 'checked' : '' }}>
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
                            <td data-label="Disponibles">{{ (int)($producto->piezas_disponibles ?? 0) }}</td>
                            <td data-label="Cantidad">
                                <input type="number"
                                    name="cantidades[{{ $producto->id_producto }}]"
                                    class="cant-input form-input"
                                    data-id="{{ $producto->id_producto }}"  {{-- NUEVO --}}
                                    min="1" step="1" max="{{ (int)($producto->piezas_disponibles ?? 0) }}"
                                    value="{{ $seleccion[$producto->id_producto] ?? '' }}"
                                    {{ ($producto->piezas_disponibles == 0 || !$producto->estado_producto) ? 'disabled' : '' }} />
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
            @continue($pedido->estado_pedido == 0)
            <option value="{{ $pedido->id_pedido }}"
                    data-user="{{ $pedido->id_user }}"
                    {{ session('pedido_reciente') == $pedido->id_pedido ? 'selected' : '' }}>
                Pedido #{{ $pedido->id_pedido }}
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
<script>
(function() {
    // Lee selección desde la URL: sel[123]=2
    function leerSeleccionDeURL() {
        const params = new URLSearchParams(location.search);
        const sel = new Map();
        for (const [k, v] of params.entries()) {
            if (k.startsWith('sel[') && k.endsWith(']')) {
                const id = k.slice(4, -1);
                const qty = parseInt(v, 10);
                if (!isNaN(qty) && qty > 0) sel.set(id, qty);
            }
        }
        return sel;
    }

    // Estado en memoria (no localStorage)
    const seleccion = leerSeleccionDeURL();

    // Sincroniza checks/cantidades visibles con el estado
    function aplicarSeleccionEnTabla() {
        document.querySelectorAll('tr[data-id]').forEach(tr => {
            const id = tr.dataset.id;
            const chk = tr.querySelector('.chk-producto');
            const qty = tr.querySelector('.cant-input');
            const max = qty ? parseInt(qty.max || '0', 10) : 0;

            if (seleccion.has(id)) {
                const val = Math.min(Math.max(seleccion.get(id), 1), isNaN(max) ? 999999 : max);
                if (chk && !chk.disabled) chk.checked = true;
                if (qty && !qty.disabled) qty.value = val;
                seleccion.set(id, val); // clamp
            } else {
                if (chk && !chk.disabled) chk.checked = false;
                if (qty && !qty.disabled) qty.value = '';
            }
        });
    }

    // Escuchar cambios y actualizar el Map
    function wireEventos() {
        document.querySelectorAll('.chk-producto').forEach(chk => {
            chk.addEventListener('change', () => {
                const id = chk.dataset.id;
                const tr = chk.closest('tr[data-id]');
                const qty = tr ? tr.querySelector('.cant-input') : null;

                if (chk.checked) {
                    let val = qty && qty.value ? parseInt(qty.value, 10) : 1;
                    const max = qty ? parseInt(qty.max || '0', 10) : 0;
                    if (isNaN(val) || val < 1) val = 1;
                    if (!isNaN(max) && max > 0) val = Math.min(val, max);
                    if (qty && !qty.disabled) qty.value = val;
                    seleccion.set(id, val);
                } else {
                    seleccion.delete(id);
                    if (qty && !qty.disabled) qty.value = '';
                }
            });
        });

        // Reemplaza tu listener actual de '.cant-input' por este:
        document.querySelectorAll('.cant-input').forEach(inp => {
        // INPUT: permitir borrar (vacío) o ceros para deseleccionar
        inp.addEventListener('input', () => {
            const id  = inp.dataset.id;
            const tr  = inp.closest('tr[data-id]');
            const chk = tr ? tr.querySelector('.chk-producto') : null;

            const raw = (inp.value || '').trim();

            // vacío o SOLO ceros => deseleccionar y NO clamping
            if (raw === '' || /^0+$/.test(raw)) {
            if (chk && !chk.disabled) chk.checked = false;
            seleccion.delete(id);
            // deja inp.value tal cual (puede quedar vacío)
            return;
            }

            // si aún no es un entero positivo, no fuerces nada (ej: mientras escribe)
            if (!/^\d+$/.test(raw)) return;

            // número válido => ahora sí clamping a [1..max]
            let val = parseInt(raw, 10);
            const max = parseInt(inp.max || '0', 10);
            if (!isNaN(max) && max > 0 && val > max) val = max;
            if (val < 1) val = 1;

            inp.value = String(val);
            if (chk && !chk.disabled) chk.checked = true;
            seleccion.set(id, val);
        });

        // BLUR: si quedó vacío/ceros, deselecciona; si quedó número, clamp final
        inp.addEventListener('blur', () => {
            const id  = inp.dataset.id;
            const tr  = inp.closest('tr[data-id]');
            const chk = tr ? tr.querySelector('.chk-producto') : null;

            const raw = (inp.value || '').trim();

            if (raw === '' || /^0+$/.test(raw)) {
            if (chk && chk.checked) chk.checked = false;
            seleccion.delete(id);
            inp.value = ''; // opcional: normalizar a vacío
            return;
            }

            if (/^\d+$/.test(raw)) {
            let val = parseInt(raw, 10);
            const max = parseInt(inp.max || '0', 10);
            if (!isNaN(max) && max > 0 && val > max) val = max;
            if (val < 1) val = 1;
            inp.value = String(val);
            if (chk && !chk.disabled) chk.checked = true;
            seleccion.set(id, val);
            }
        });
        });


    }

    // Limpia sel[*] de una URL y agrega los actuales
    function conSeleccionEnURL(urlString) {
        const url = new URL(urlString, location.origin);
        // elimina claves previas sel[...]
        [...url.searchParams.keys()].forEach(k => {
            if (k.startsWith('sel[') && k.endsWith(']')) url.searchParams.delete(k);
        });
        // agrega el estado actual
        seleccion.forEach((qty, id) => {
            url.searchParams.append(`sel[${id}]`, String(qty));
        });
        return url.toString();
    }

    // 3.a) Paginación: interceptar y reescribir href con sel[*]
    document.querySelectorAll('.pagination a.page-link, .pagination a').forEach(a => {
        a.addEventListener('click', (e) => {
            e.preventDefault();
            const href = conSeleccionEnURL(a.href);
            location.href = href;
        });
    });

    // 3.b) Filtros (GET): antes de enviar, agregamos sel[*] como hidden inputs
    const formFiltros = document.getElementById('form-filtros');
    if (formFiltros) {
        formFiltros.addEventListener('submit', (e) => {
            // El envío es por GET, más robusto hacerlo vía URL
            e.preventDefault();
            const action = formFiltros.getAttribute('action') || location.pathname;
            // Combinar filtros actuales + seleccion
            const formData = new FormData(formFiltros);
            const url = new URL(action, location.origin);
            // Pasar todos los campos del form a la URL
            for (const [k, v] of formData.entries()) {
                if (v !== '') url.searchParams.append(k, v);
            }
            // Agregar sel[*]
            seleccion.forEach((qty, id) => url.searchParams.append(`sel[${id}]`, String(qty)));
            location.href = url.toString();
        });
    }

    const btnLimpiar = document.getElementById('btn-limpiar');
    if (btnLimpiar) {
      btnLimpiar.addEventListener('click', function(e) {
        e.preventDefault();
        // Navega a carro.create pero agregando sel[*] actuales
        location.href = conSeleccionEnURL(this.href);
      });
    }


    // 3.c) Antes del POST final, inyectar todos los seleccionados como hidden
    const formSeleccion = document.getElementById('form-seleccion');
    if (formSeleccion) {
    formSeleccion.addEventListener('submit', () => {
        // 1) borra inyecciones previas e inyecta hidden (tu código existente)
        formSeleccion.querySelectorAll('input.__dyn').forEach(n => n.remove());
        seleccion.forEach((qty, id) => {
        const hidId = document.createElement('input');
        hidId.type = 'hidden';
        hidId.name = 'productos_seleccionados[]';
        hidId.value = id;
        hidId.className = '__dyn';
        formSeleccion.appendChild(hidId);

        const hidQty = document.createElement('input');
        hidQty.type = 'hidden';
        hidQty.name = `cantidades[${id}]`;
        hidQty.value = String(qty);
        hidQty.className = '__dyn';
        formSeleccion.appendChild(hidQty);
        });

        // 2) evita duplicados: no envíes los inputs visibles
        document.querySelectorAll('.chk-producto, .cant-input').forEach(el => {
        el.disabled = true;          // más simple
        // alternativamente: el.setAttribute('data-old-name', el.name); el.name = '';
        });
    });
    }


    // Al cargar: aplica selección y conecta eventos
    aplicarSeleccionEnTabla();
    wireEventos();
})();
</script>

</body>
</html>
