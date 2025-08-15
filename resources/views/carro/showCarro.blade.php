<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/carro/showCarro.css') }}">
    <title>Detalle(s) de Carro</title>
    <style>
        table { border-collapse: collapse; width: 100%; margin-bottom: 30px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: center; }
        th { background-color: #eee; }
        img { max-width: 120px; height: auto; }
        h2 { margin-top: 40px; }
        button {
            cursor: pointer;
            padding: 4px 10px;
            margin: 2px;
        }
        form.inline {
            display: inline;
        }

        /* --------- Solo responsividad (sin cambiar tu HTML/Blade) --------- */
        * { box-sizing: border-box; }
        html { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; }
        body { margin: 0; padding: 16px; color: #222; background: #fff; }
        h1 { margin: 0 0 12px; font-size: clamp(1.25rem, 1rem + 1.2vw, 2rem); }
        h2 { font-size: clamp(1.1rem, 0.95rem + 0.7vw, 1.5rem); }

        /* Evitar desbordes en imágenes dentro de celdas */
        td img { display: block; margin: 0 auto; max-width: min(120px, 100%); height: auto; }

        @media (max-width: 992px) {
          body { padding: 12px; }
        }

        /* En móviles/tablets: tabla con scroll horizontal y acciones apiladas */
        @media (max-width: 768px) {
          table {
            display: block;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border: 1px solid #e6e6e6;
          }
          thead, tbody, tr, th, td { white-space: nowrap; }

          /* Formularios y controles a ancho completo para facilidad de uso */
          form { max-width: 100%; }
          form select,
          form button {
            width: 100%;
            max-width: 100%;
            margin: 6px 0;
          }

          /* Botones de acciones dentro de la tabla en columna vertical */
          td:last-child a,
          td:last-child form {
            display: block;
            width: 100%;
            margin: 6px 0;
          }
          td:last-child button,
          td:last-child a button {
            width: 100%;
          }
        }

        /* Móviles muy pequeños: permitir saltos de línea y toque cómodo */
        @media (max-width: 480px) {
          thead, tbody, tr, th, td { white-space: normal; }
          th, td { padding: 6px; }
          button, select { min-height: 44px; } /* área táctil mínima */
        }
        /* ------------------------------------------------------------------ */
    </style>
</head>
<body>
    <br><hr class="hr-grueso"><center><h1>Detalle del carro</h1></center><hr class="hr-grueso">

    @php
        // Normaliza variable para trabajar con uno o varios carros
        $listaCarros = isset($carros) ? $carros : (isset($carro) ? collect([$carro]) : collect([]));

        // Obtener IDs únicos de productos para calcular reservas globales
        $productoIds = $listaCarros->flatMap(fn($c) => $c->productos->pluck('id_producto'))->unique();

        $reservasGlobales = \App\Models\CarroProducto::select('id_producto')
            ->selectRaw('SUM(cantidad) as reservadas')
            ->whereIn('id_producto', $productoIds)
            ->groupBy('id_producto')
            ->pluck('reservadas', 'id_producto');
    @endphp

    @if($listaCarros->isEmpty())
        <p>No se encontraron carros para mostrar.</p>
    @else
        @foreach ($listaCarros as $carroItem)
            @php
                $total = 0;
                $pedido = $carroItem->pedido ?? null;
                $pedidoCerrado = $pedido && $pedido->estado_pedido == 0;
            @endphp

            <h2>
                Carro #{{ $carroItem->id_carro }}
                @if($carroItem->user)
                    - Usuario: {{ $carroItem->user->nombre_usuario }}
                @endif
            </h2>

            @if ($carroItem->productos->isEmpty())
                <p>Este carro no tiene productos.</p>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>ID Producto</th>
                            <th>Nombre</th>
                            <th>Imagen</th>
                            <th>Material</th>
                            <th>Color</th>
                            <th>Tamaño</th>
                            <th>Piezas disponibles</th>
                            <th>Cantidad</th>
                            <th>Precio unitario</th>
                            <th>Subtotal</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($carroItem->productos as $producto)
                            @php
                                $reservado = $reservasGlobales[$producto->id_producto] ?? 0;
                                $reservadoEnEsteCarro = $producto->pivot->cantidad;
                                $disponibles = max(0, $producto->piezas - ($reservado - $reservadoEnEsteCarro));
                                $cantidad = $reservadoEnEsteCarro;
                                $subtotal = $cantidad * $producto->precio_unitario;
                                $total += $subtotal;
                            @endphp
                            <tr>
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
                                <td>{{ $disponibles }}</td>
                                <td>{{ $cantidad }}</td>
                                <td>${{ number_format($producto->precio_unitario, 2) }}</td>
                                <td>${{ number_format($subtotal, 2) }}</td>
                                <td>
                                    @if(!$pedidoCerrado)
                                        <a href="{{ route('carro.edit', ['id_carro' => $carroItem->id_carro, 'id_producto' => $producto->id_producto]) }}">
                                            <button type="button">Editar</button>
                                        </a>

                                        <form action="{{ route('carro.eliminarProducto', ['id_carro' => $carroItem->id_carro, 'id_producto' => $producto->id_producto]) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar este producto?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit">Eliminar producto</button>
                                        </form>
                                    @else
                                        <span style="color: gray;">Pedido cerrado</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <p><strong>Total del carrito:</strong> ${{ number_format($total, 2) }}</p>
            @endif
                <p>
                    <form action="{{ route('carro.destroy', $carroItem->id_carro) }}" method="POST" onsubmit="return confirm('¿Seguro que quieres eliminar todo este carro?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" style="background-color:#d9534f; color:#fff; border:none; padding:6px 12px; cursor:pointer;">
                            Eliminar carro completo
                        </button>
                    </form>
                </p>
            {{-- Formulario para cerrar pedido solo si no está cerrado --}}
            @if($pedido && !$pedidoCerrado)
                @php
                    $usuario = $carroItem->user;

                    $creditosActivos = \App\Models\Credito::where('id_user', $usuario->id_user)
                        ->where('estado', 1)
                        ->whereDate('fecha_vencimiento', '>=', now())
                        ->get();

                    $creditosVencidos = \App\Models\Credito::where('id_user', $usuario->id_user)
                        ->where('estado', 1)
                        ->whereDate('fecha_vencimiento', '<', now())
                        ->get();

                    // Condición para bloquear creación de nuevo crédito:
                    // No permitir si tiene 3 o más créditos activos o al menos 1 vencido.
                    $puedeCrearCredito = ($creditosActivos->count() < 3);

                    // Filtra créditos para mostrar opciones válidas (que no excedan saldo)
                    $creditosDisponibles = $creditosActivos->filter(function ($credito) use ($total) {
                        return ($credito->saldo_total + $total) <= 10000;
                    });

                    // Para bloquear cierre a crédito si excede saldo o tiene historial bloqueado
                    $totalCreditos = $creditosActivos->sum('saldo_total');
                    $bloqueadoPorSaldo = ($totalCreditos + $total) > 10000;
                    $bloqueadoPorHistorial = method_exists($usuario, 'tienePagosAtrasadosSinAbonar') && $usuario->tienePagosAtrasadosSinAbonar();
                    $bloqueado = $bloqueadoPorSaldo || $bloqueadoPorHistorial;
                @endphp


                <form action="{{ route('pedido.cerrar', $pedido->id_pedido) }}" method="POST">
                    @csrf
                    <input type="hidden" name="total" value="{{ $total }}" />

                    @if($bloqueado)
                        <p style="color:red;">
                            <strong>No puedes cerrar este pedido a crédito:</strong><br>
                            @if($bloqueadoPorSaldo)
                                - El total de créditos más este pedido excede los $10,000.<br>
                            @endif
                            @if($bloqueadoPorHistorial)
                                - Tienes pagos atrasados sin abonar. Tu acceso a crédito está bloqueado.<br>
                            @endif
                            Puedes cerrarlo como <strong>contado</strong>.
                        </p>
                    @endif

                    <label for="metodo_pago_{{ $pedido->id_pedido }}">Método de pago:</label>
                    <select name="metodo_pago" required onchange="mostrarCreditos(this, {{ $pedido->id_pedido }})">
                        <option value="">-- Selecciona --</option>
                        <option value="contado">Contado</option>
                        @if(!$bloqueado)
                            <option value="credito">Crédito</option>
                        @endif
                    </select>

                    <div id="credito-opciones-{{ $pedido->id_pedido }}" style="display:none; margin-top:8px;">
                        @if(!$bloqueado)
                            <label>Seleccionar crédito:</label>
                            <select name="id_credito" class="select-credito">
                                @if($puedeCrearCredito)
                                    <option value="">-- Crear nuevo crédito --</option>
                                @endif
                                @foreach($creditosDisponibles as $credito)
                                    <option value="{{ $credito->id_credito }}">
                                        Crédito #{{ $credito->id_credito }} - Saldo: ${{ number_format($credito->saldo_total, 2) }}
                                    </option>
                                @endforeach
                            </select>

                            @if(!$puedeCrearCredito)
                                <p style="color:orange; font-style: italic;">
                                    Ya tienes 3 créditos activos o créditos vencidos. No puedes crear uno nuevo, pero puedes usar los existentes.
                                </p>
                            @endif
                        @endif
                    </div>

                    <button type="submit" style="margin-top:8px;">Cerrar pedido</button>
                </form>

            @elseif($pedidoCerrado)
                <p style="color: gray;"><strong>Pedido cerrado</strong></p>
            @endif

            <hr>
        @endforeach
    @endif

    <br>
    <a href="{{ url('/carro') }}">Volver al listado</a>

    <script>
        function mostrarCreditos(select, idPedido) {
            const div = document.getElementById('credito-opciones-' + idPedido);
            div.style.display = select.value === 'credito' ? 'block' : 'none';
        }
    </script>

</body>
</html>
