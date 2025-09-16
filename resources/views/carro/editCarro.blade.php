<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/carro/editCarro.css') }}">
    <title>Editar carro</title>
</head>
<body>
    <div class="page-container">
        <main class="content">
        <x-barracreate/>
        <a id="top"></a>
            <section>
                <br><hr class="hr-grueso"><center><h1>Editar carro</h1></center><hr class="hr-grueso"><br>
                
                @php
                    $pedidoCerrado  = $carro->pedido && $carro->pedido->estado_pedido == 0;
                    $descontinuado  = !$productoActual->estado_producto;
                @endphp

                @if(session('error'))
                    <p style="color: red;">{{ session('error') }}</p>
                @endif

                @if(session('success'))
                    <p style="color: green;">{{ session('success') }}</p>
                @endif

                @if($pedidoCerrado)
                    <p style="color:red;"><strong>Este pedido ya está cerrado. No puedes editar el carro.</strong></p>
                @else

                <form action="{{ route('carro.edit', ['id_carro' => $carro->id_carro, 'id_producto' => $productoActual->id_producto]) }}" method="GET" >
                    <input type="hidden" name="sel_id"  value="{{ $selId ?? $productoActual->id_producto }}">
                    <input type="hidden" name="sel_qty" value="{{ $selQty ?? $cantidad }}">
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
                        <div>
                            <button type="submit" class="btn btn-registrar">Filtrar</button>
                            <a id="btn-limpiar"
                            href="{{ route('carro.edit', ['id_carro' => $carro->id_carro, 'id_producto' => $productoActual->id_producto, 'sel_id' => $selId, 'sel_qty' => $selQty]) }}"
                            class="btn btn-gray">Limpiar</a>
                        </div>
                    </div>
                </form>
                
                    <form action="{{ route('carro.update', ['carro' => $carro->id_carro, 'id_producto' => $productoActual->id_producto]) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <h3>Selecciona el producto</h3>
                        <div class="table-wrap">
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
                                        <th>Disponibles</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($productos as $producto)
                                        @php
                                            $sinStock      = $producto->piezas_disponibles == 0;
                                            $inactivo      = $producto->estado_producto == 0;

                                            // seleccionado actual desde el controller:
                                            $esSeleccionado = isset($selId) && ((int)$selId === (int)$producto->id_producto);

                                            // Mantener seleccionable el original; para otros, bloquear si sin stock o inactivo
                                            $esOriginal      = ((int)$producto->id_producto === (int)$productoActual->id_producto);
                                            $deshabilitado   = (!$esOriginal && ($sinStock || $inactivo));
                                        @endphp

                                        <tr class="{{ $sinStock || $inactivo ? 'sin-stock' : '' }}"data-id="{{ $producto->id_producto }}"
                                        data-disp="{{ $producto->piezas_disponibles }}"
                                        data-estado="{{ (int)$producto->estado_producto }}">
                                            <td data-label="Seleccionar">
                                                <input type="radio"
                                                    class="rad-producto"
                                                    name="id_producto"
                                                    value="{{ $producto->id_producto }}"
                                                    {{ $esSeleccionado ? 'checked' : '' }}
                                                    {{ $deshabilitado ? 'disabled' : '' }}
                                                >
                                            </td>
                                            <td data-label="Imagen">
                                                @if (!empty($producto->imagen)) 
                                                    <img src="{{ Storage::disk('s3')->url($producto->imagen) }}" alt="Foto de producto" width="200">
                                                @else
                                                    <span>Sin imagen</span>
                                                @endif
                                            </td>
                                            <td data-label="Nombre">{{ $producto->nombre }}</td>
                                            <td data-label="Material">{{ $producto->material }}</td>
                                            <td data-label="Color">{{ $producto->color }}</td>
                                            <td data-label="Tamaño">{{ $producto->tamanio }}</td>
                                            <td data-label="Precio">${{ number_format($producto->precio_unitario, 2) }}</td>
                                            <td data-label="Disponibles" class="{{ $sinStock ? 'resaltado' : '' }}">{{ $producto->piezas_disponibles }}</td>
                                            <td data-label="Estado">{{ $producto->estado_producto ? 'Activo' : 'Inactivo' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 d-flex justify-content-center">
                            {{ $productos ->appends(request()->except('page') + [
                                    'sel_id'  => $selId ?? $productoActual->id_producto,
                                    'sel_qty' => $selQty ?? $cantidad,
                                ])->links('pagination::bootstrap-5') }}
                        </div>

                        @if($paginaCorrecta)
                        <div class="mt-3 text-center">
                            <a href="{{ route('carro.edit', [
                                'id_carro'    => $carro->id_carro,
                                'id_producto' => $productoActual->id_producto,
                                'page'        => $paginaCorrecta,
                                'sel_id'      => $selId,
                                'sel_qty'     => $selQty,
                                'navegacion'  => 1,
                            ] + request()->except(['page','navegacion','sel_id','sel_qty'])) }}" class="btn btn-agregar">Página del producto actual
                            </a>
                        </div>
                        @endif

                        @php
                            // Producto actualmente "seleccionado" (si está en la página; si no, usa el original)
                            $prodSel = $productos->firstWhere('id_producto', $selId) ?: $productoActual;

                            $selSinStock = ($prodSel->piezas_disponibles ?? 0) == 0;
                            $selInactivo = (int)($prodSel->estado_producto ?? 0) == 0;

                            // Si es el original e inactivo, mantén tu regla: solo permitir reducir (max = $cantidad original)
                            $esOriginalSel = ((int)$prodSel->id_producto === (int)$productoActual->id_producto);
                            $maxCantidad   = ($esOriginalSel && $selInactivo) ? $cantidad : ($prodSel->piezas_disponibles ?? $productoActual->piezas_disponibles);
                        @endphp

                        <center><br>
                        <label for="cantidad">Cantidad:</label>
                        <input type="number"
                            name="cantidad"
                            id="cantidad"
                            min="1"
                            max="{{ (int)$maxCantidad }}"
                            value="{{ (int)$selQty }}"
                            required
                            class="cant-input form-input"
                        >

                        @if($esOriginalSel && $selInactivo)
                            <p style="color: red;">
                                Este producto está inactivo. Solo puedes reducir la cantidad actual (máximo {{ $cantidad }}).
                            </p>
                        @endif

                        <br><br>
                        <label for="id_pedido">Selecciona un pedido existente o crea uno nuevo:</label>
                        <select name="id_pedido" class="form-input">
                            <option value="" disabled selected>-- Ninguno --</option>
                            <option value="nuevo">-- Crear nuevo pedido --</option>
                            @foreach($pedidosUsuario as $pedido)
                                @php
                                    $deshabilitado = ($pedido->estado_pedido == 0);
                                @endphp
                                @if(!$deshabilitado)
                                    <option value="{{ $pedido->id_pedido }}"
                                        {{ $carro->id_pedido == $pedido->id_pedido ? 'selected' : '' }}>
                                        Pedido #{{ $pedido->id_pedido }}
                                    </option>
                                @endif
                            @endforeach
                        </select><br><br>
                        
                        <button type="submit" class="btn btn-registrar">Actualizar Carro</button>

                    </form>
                @endif <br><br>

                <a href="{{ route('carro.index') }}" class="btn btn-danger">Cancelar</a>
                <a href="#top" class="btn btn-agregar" aria-label="Ir arriba">Ir arriba</a></center>
            </section>
        </main>
        <x-footer/>
    </div>
    <script>
        (function () {
        const qs  = (s, c) => (c || document).querySelector(s);
        const qsa = (s, c) => Array.from((c || document).querySelectorAll(s));
        const i10 = (v, d = 0) => { const n = parseInt(v, 10); return Number.isNaN(n) ? d : n; };

        function getSelFromUIOnly() {
            const r   = qs('.rad-producto:checked');
            const qty = qs('#cantidad');
            return { id: r ? String(r.value) : null, qty: qty && qty.value ? String(qty.value) : null };
        }
        function getSelFromURLOnly() {
            const u = new URL(location.href);
            const id  = u.searchParams.get('sel_id');
            const qty = u.searchParams.get('sel_qty');
            return { id: id || null, qty: qty || null };
        }

        function getEffectiveSel() {
            let { id, qty } = getSelFromUIOnly();
            if (!id) {
            const f = getSelFromURLOnly();
            id  = id  || f.id;
            qty = qty || f.qty;
            }
            return { id, qty };
        }

        function pushSelToURL({ id, qty }) {
            const u = new URL(location.href);
            if (id)  u.searchParams.set('sel_id', id);  else u.searchParams.delete('sel_id');
            if (qty) u.searchParams.set('sel_qty', qty); else u.searchParams.delete('sel_qty');
            history.replaceState(null, '', u.toString());
        }

        function withSelParams(url) {
            const u = new URL(url, location.origin);
            const { id, qty } = getEffectiveSel();
            u.searchParams.delete('sel_id'); u.searchParams.delete('sel_qty');
            if (id)  u.searchParams.set('sel_id', id);
            if (qty) u.searchParams.set('sel_qty', qty);
            return u.toString();
        }

        function applyQtyBoundsFromRow(row) {
            const qtyI = qs('#cantidad');
            if (!row || !qtyI) return;
            const disp = i10(row.getAttribute('data-disp'), 0);
            qtyI.min = '1';
            qtyI.max = String(Math.max(0, disp));

            const raw = (qtyI.value || '').trim();
            if (raw === '' || /^0+$/.test(raw)) return;

            let v = i10(raw, 1);
            const max = i10(qtyI.max || '1', 1);
            if (max > 0 && v > max) v = max;
            if (v < 1) v = 1;
            qtyI.value = String(v);
        }

        function syncHiddenFilters() {
            const { id, qty } = getEffectiveSel();
            const hidId  = qs('input[name="sel_id"]');
            const hidQty = qs('input[name="sel_qty"]');
            if (hidId)  hidId.value  = id  || '';
            if (hidQty) hidQty.value = qty || '';
        }

        (function ensureURLHasSelection() {
            const fromURL = getSelFromURLOnly();
            if (!fromURL.id || !fromURL.qty) {
            const eff = getEffectiveSel();
            if (eff.id || eff.qty) pushSelToURL(eff);
            }
        })();

        (function applySelectionFromURL() {
            const { id, qty } = getSelFromURLOnly();
            const qtyI = qs('#cantidad');

            if (id) {
            const radio = qs(`.rad-producto[value="${CSS.escape(id)}"]`);
            if (radio && !radio.disabled) {
                radio.checked = true;
                applyQtyBoundsFromRow(radio.closest('tr[data-id]'));
            }
            }
            if (qtyI && qty) {
            const max = i10(qtyI.max || '0', 0);
            let v = i10(qty, 1);
            if (max > 0) v = Math.min(v, max);
            if (v < 1) v = 1;
            qtyI.value = String(v);
            }
        })();

        syncHiddenFilters();

        qsa('.rad-producto').forEach(r => {
            r.addEventListener('change', () => {
            applyQtyBoundsFromRow(r.closest('tr[data-id]'));
            const eff = getEffectiveSel();
            pushSelToURL(eff);
            syncHiddenFilters();
            });
        });

        const qtyI = qs('#cantidad');
        if (qtyI) {
            qtyI.addEventListener('input', () => {
            const raw = (qtyI.value || '').trim();
            const radio = qs('.rad-producto:checked');

            if (raw === '' || /^0+$/.test(raw)) {
                if (radio && !radio.disabled) radio.checked = false;
                pushSelToURL({ id: null, qty: null });
                syncHiddenFilters();
                return;
            }

            if (!/^\d+$/.test(raw)) return;

            let val = parseInt(raw, 10);
            let max = i10(qtyI.max || '0', 0);
            const row = radio ? radio.closest('tr[data-id]') : null;
            if (row) max = i10(row.getAttribute('data-disp'), max);

            if (max > 0 && val > max) val = max;
            if (val < 1) val = 1;

            qtyI.value = String(val);

            const id = radio ? radio.value : getSelFromURLOnly().id;
            pushSelToURL({ id: id || null, qty: String(val) });
            syncHiddenFilters();
            });

            qtyI.addEventListener('blur', () => {
            const raw = (qtyI.value || '').trim();
            const radio = qs('.rad-producto:checked');

            if (raw === '' || /^0+$/.test(raw)) {
                if (radio && radio.checked) radio.checked = false;
                qtyI.value = '';
                pushSelToURL({ id: null, qty: null });
                syncHiddenFilters();
                return;
            }

            if (/^\d+$/.test(raw)) {
                let val = parseInt(raw, 10);
                let max = i10(qtyI.max || '0', 0);
                const row = radio ? radio.closest('tr[data-id]') : null;
                if (row) max = i10(row.getAttribute('data-disp'), max);

                if (max > 0 && val > max) val = max;
                if (val < 1) val = 1;

                qtyI.value = String(val);
                const id = radio ? radio.value : getSelFromURLOnly().id;
                pushSelToURL({ id: id || null, qty: String(val) });
                syncHiddenFilters();
            }
            });
        }

        qsa('.pagination a.page-link, .pagination a').forEach(a => {
            a.addEventListener('click', (e) => {
            e.preventDefault();
            pushSelToURL(getEffectiveSel());
            location.href = withSelParams(a.href);
            });
        });

        const formFiltros = document.getElementById('form-filtros');
        if (formFiltros) {
            formFiltros.addEventListener('submit', (e) => {
            e.preventDefault();
            const action   = formFiltros.getAttribute('action') || location.pathname;
            const formData = new FormData(formFiltros);
            const url      = new URL(action, location.origin);

            for (const [k, v] of formData.entries()) {
                if (v !== '') url.searchParams.append(k, v);
            }
            const { id, qty } = getEffectiveSel();
            if (id)  url.searchParams.set('sel_id', id);
            if (qty) url.searchParams.set('sel_qty', qty);

            location.href = url.toString();
            });
        }

        const btnLimpiar = document.getElementById('btn-limpiar');
        if (btnLimpiar) {
            btnLimpiar.addEventListener('click', (e) => {
            e.preventDefault();
            pushSelToURL(getEffectiveSel());
            location.href = withSelParams(btnLimpiar.href);
            });
        }
        })();
    </script>
</body>
</html>
