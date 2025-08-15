<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Carro</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/carro/createCarro.css') }}">


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

    /* NUEVO: desactiva checkbox e input cantidad si no hay piezas disponibles */
    function desactivarProductosSinStock() {
        const filas = document.querySelectorAll('table tbody tr');

        filas.forEach(fila => {
            // 8ª columna = "Piezas disponibles"
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
        desactivarProductosSinStock(); // <-- llamada añadida
    });
    </script>

</head>
<body>
  <a id="top"></a>
    <br><hr class="hr-grueso"><center><h1>Crear nuevo carro</h1></center><hr class="hr-grueso">

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
                        @php
                            // Si viene null o negativo, lo normalizamos a 0
                            $disp = max(0, (int)($producto->piezas_disponibles ?? 0));
                        @endphp
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
