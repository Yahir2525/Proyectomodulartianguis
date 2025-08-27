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
<br><x-barracreate/>
    <a id="top"></a>
    <br><hr class="hr-grueso"><center><h1>Crear nuevo carro</h1></center><hr class="hr-grueso">

    @if(session('error'))
        <p style="color: red;">{{ session('error') }}</p>
    @endif

    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    {{-- 🔍 Buscador de productos --}}
    <form action="{{ route('carro.create') }}" method="GET" class="mb-3">
        <label for="buscar_producto">Buscar producto:</label>
        <input type="text" name="buscar" id="buscar_producto" placeholder="Ej. cortina, mesa..." value="{{ request('buscar') }}">
        <button type="submit">Buscar</button>
    </form>

    <form action="{{ route('carro.agregarMultiples') }}" method="POST">
        @csrf
        <h3>Selecciona productos</h3><br>
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
                            @if (!empty($producto->imagen)) 
                                <img src="{{ Storage::disk('s3')->url($producto->imagen) }}" alt="Foto de producto" width="250">
                            @else
                                <span>Sin imagen</span>
                            @endif
                        </td>
                        <td>{{ $producto->nombre }}</td>
                        <td>{{ $producto->material }}</td>
                        <td>{{ $producto->color }}</td>
                        <td>{{ $producto->tamanio }}</td>
                        <td>${{ number_format($producto->precio_unitario, 2) }}</td>
                        @php $disp = max(0, (int)($producto->piezas_disponibles ?? 0)); @endphp
                        <td>{{ $disp }}</td>
                        <td>
                            <input
                                type="number"
                                name="cantidades[{{ $producto->id_producto }}]"
                                class="cant-input"
                                min="1"
                                step="1"
                                max="{{ $disp }}"
                                {{ $disp === 0 ? 'disabled' : '' }}
                                inputmode="numeric"
                                pattern="[0-9]*"
                            />
                        </td>
                    </tr>
                    @endif
                @endforeach
            </tbody>
        </table>

        {{-- 🔗 Links de paginación --}}
        <div class="mt-4 d-flex justify-content-center">
            {{ $productos->appends(['buscar' => request('buscar')])->links('pagination::bootstrap-5') }}
        </div>

        <center>
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

    <br><br>
    <a href="/carro">Cancelar</a>
    <a href="#top" aria-label="Ir arriba">Ir arriba</a></center>
</body>
</html>
