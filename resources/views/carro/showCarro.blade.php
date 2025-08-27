<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/carro/showCarro.css') }}">
    <title>Detalle(s) de Carro</title>
</head>
<body>
<br><x-barrageneral/>
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
                            <th>Cantidad</th>
                            <th>Precio unitario</th>
                            <th>Subtotal</th>
                            <th>Editar</th>
                            <th>Eliminar</th>
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
                                <td>{{ $cantidad }}</td>
                                <td>${{ number_format($producto->precio_unitario, 2) }}</td>
                                <td>${{ number_format($subtotal, 2) }}</td>
                                <td>
                                    @if(!$pedidoCerrado)
                                        <a href="{{ route('carro.edit', ['id_carro' => $carroItem->id_carro, 'id_producto' => $producto->id_producto]) }}">
                                            <button type="button">Editar</button>
                                        </a>
                                </td>
                                <td>
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

                <br><p><strong>Total del carrito:</strong> ${{ number_format($total, 2) }}</p>
            @endif
            @if($pedido && $pedido->estado_pedido == 1)
                <p>
                    <form action="{{ route('carro.destroy', $carroItem->id_carro) }}" method="POST" onsubmit="return confirm('¿Seguro que quieres eliminar todo este carro?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" style="background-color:#d9534f; color:#fff; border:none; padding:6px 12px; cursor:pointer;">
                            Eliminar carro completo
                        </button>
                    </form>
                </p>
            @endif
            @if($pedido && !$pedidoCerrado)
                @php
                    $usuario = $carroItem->user;

                    // === Créditos activos (estado=1) ===
                    $creditosTodos = \App\Models\Credito::where('id_user', $usuario->id_user)
                        ->where('estado', 1)
                        ->get();

                    // Vigentes (no vencidos por fecha)
                    $creditosVigentes = $creditosTodos->filter(fn($c) => $c->fecha_vencimiento >= now());

                    // Vencidos por fecha
                    $creditosVencidos = $creditosTodos->filter(fn($c) => $c->fecha_vencimiento < now());

                    // === Reglas de negocio ===
                    // Permitir hasta 2 vencidos con saldo > 0
                    $creditosVencidosConSaldo = $creditosVencidos->filter(fn($c) => (float)$c->saldo_total > 0);
                    $bloqueadoPorHistorial = $creditosVencidosConSaldo->count() > 2;

                    // Tope $10,000 considerando vigentes
                    $totalCreditosVigentes = $creditosVigentes->sum('saldo_total');
                    $bloqueadoPorSaldo = ($totalCreditosVigentes + $total) > 10000;

                    // Nivel
                    $nivelUsuario = strtolower((string)($usuario->nivel_usuario ?? ''));
                    $bloqueadoPorNivel = ($nivelUsuario === 'malo');

                    // Puede crear nuevo si tiene < 3 activos (incluye vencidos)
                    $puedeCrearCredito = ($creditosTodos->count() < 3);

                    // Usables: vigentes que no rebasan tope con este pedido
                    $creditosDisponibles = $creditosVigentes->filter(fn($c) => ($c->saldo_total + $total) <= 10000);

                    // Si no hay usables y TAMPOCO puede crear nuevo, bloquear opción crédito
                    $sinCreditosUsables = $creditosDisponibles->isEmpty() && !$puedeCrearCredito;

                    $bloqueado = $bloqueadoPorSaldo || $bloqueadoPorHistorial || $bloqueadoPorNivel || $sinCreditosUsables;
                @endphp
                <form action="{{ route('pedido.cerrar', $pedido->id_pedido) }}" method="POST">
                    @csrf
                    <input type="hidden" name="total" value="{{ $total }}" />

                    @if($bloqueado)
                        <p style="color:red;">
                            <strong>No puedes cerrar este pedido a crédito:</strong><br>
                            @if($bloqueadoPorSaldo)
                                - El total de créditos vigentes más este pedido excede los $10,000.<br>
                            @endif
                            @if($bloqueadoPorHistorial)
                                - Tienes más de 2 créditos vencidos con saldo pendiente.<br>
                            @endif
                            @if($bloqueadoPorNivel)
                                - Tu nivel actual es <strong>"malo"</strong>. Solo puedes cerrar pedidos <strong>a contado</strong>.<br>
                            @endif
                            @if($sinCreditosUsables)
                                - No tienes créditos vigentes disponibles y no puedes crear uno nuevo.<br>
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
