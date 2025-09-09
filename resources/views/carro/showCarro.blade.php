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
<div class="page-container">
<main class="content">
<br><x-barrageneral/>
<section>
    <br><hr class="hr-grueso"><h1>Detalles del carro</h1><hr class="hr-grueso">

    <form action="{{ url('/carro/showCarro') }}" method="GET" class="buscar">
        <label for="buscar">Buscar carro:</label>
        <input type="text" id="buscar" name="buscar" placeholder="Ej. 21 o Carlitos"
        list="{{ Auth::user()->can('edit carro') ? 'usuarios' : '' }}"
        value="{{ request('buscar') }}" />

        @can('edit carro')
        <datalist id="usuarios">
            @foreach($usuarios as $usuario)
                <option value="{{ $usuario->nombre_usuario }}"></option>
            @endforeach
        </datalist>
        @endcan
        <input type="submit" value="Buscar" class="btn btn-warning" />
    </form>

    @php
        $listaCarros = isset($carros) ? $carros : (isset($carro) ? collect([$carro]) : collect([]));

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
                <div class="table-responsive table-wrap">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID Producto</th>
                                <th>Nombre</th>
                                <th>Imagen</th>
                                <th>Material</th>
                                <th>Color</th>
                                <th>Tamaño</th>
                                <th>Cantidad</th>
                                <th>Precio</th>
                                <th>Subtotal</th>
                                <th>Editar</th>
                                <th>Eliminar producto</th>
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
                                    <td data-label="ID producto">{{ $producto->id_producto }}</td>
                                    <td data-label="Nombre">{{ $producto->nombre }}</td>
                                    <td data-label="Imagen">
                                        @if (!empty($producto->imagen)) 
                                            <img src="{{ Storage::disk('s3')->url($producto->imagen) }}" alt="Foto de producto">
                                        @else
                                            <span>Sin imagen</span>
                                        @endif
                                    </td>
                                    <td data-label="Material">{{ $producto->material }}</td>
                                    <td data-label="Color">{{ $producto->color }}</td>
                                    <td data-label="Tamaño">{{ $producto->tamanio }}</td>
                                    <td data-label="Cantidad">{{ $cantidad }}</td>
                                    <td data-label="Precio">${{ number_format($producto->precio_unitario, 2) }}</td>
                                    <td data-label="Subtotal">${{ number_format($subtotal, 2) }}</td>
                                    <td data-label="Editar">
                                        @if(!$pedidoCerrado)
                                            <a href="{{ route('carro.edit', ['id_carro' => $carroItem->id_carro, 'id_producto' => $producto->id_producto]) }}" class="btn btn-edit">
                                                Editar
                                            </a>
                                        @else
                                            <span class="badge bg-gray">Pedido cerrado</span>
                                        @endif
                                    </td>
                                    <td data-label="Eliminar producto">
                                        @if(!$pedidoCerrado)
                                            <form action="{{ route('carro.eliminarProducto', ['id_carro' => $carroItem->id_carro, 'id_producto' => $producto->id_producto]) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar este producto?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger">Eliminar</button>
                                            </form>
                                        @else
                                            <span class="badge bg-gray">Pedido cerrado</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <br><p><strong>Total del carrito:</strong> ${{ number_format($total, 2) }}</p>
            @endif

            @if($pedido && $pedido->estado_pedido == 1)
                <p>
                    <form action="{{ route('carro.destroy', $carroItem->id_carro) }}" method="POST" onsubmit="return confirm('¿Seguro que quieres eliminar todo este carro?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Eliminar carro completo</button>
                    </form>
                </p>
            @endif

            @if($pedido && !$pedidoCerrado)
                @php
                    $usuario = $carroItem->user;
                    $creditosTodos = \App\Models\Credito::where('id_user', $usuario->id_user)->where('estado', 1)->get();
                    $creditosVigentes = $creditosTodos->filter(fn($c) => $c->fecha_vencimiento >= now());
                    $creditosVencidos = $creditosTodos->filter(fn($c) => $c->fecha_vencimiento < now());
                    $totalExcede = $total > 10000;
                    $bloqueadoPorHistorial = $creditosVencidos->count() >= 2;
                    $totalCreditosVigentes = $creditosVigentes->sum('saldo_total');
                    $bloqueadoPorSaldo = ($totalCreditosVigentes + $total) > 10000;
                    $nivelUsuario = strtolower((string)($usuario->nivel_usuario ?? ''));
                    $bloqueadoPorNivel = ($nivelUsuario === 'malo');
                    $puedeCrearCredito = ($creditosTodos->count() < 3);
                    $creditosDisponibles = $creditosVigentes->filter(fn($c) => ($c->saldo_total + $total) <= 10000);
                    $bloqueado = $bloqueadoPorSaldo || $bloqueadoPorHistorial || $bloqueadoPorNivel || $sinCreditosUsables;
                @endphp

                <form action="{{ route('pedido.cerrar', $pedido->id_pedido) }}" method="POST">
                    @csrf
                    <input type="hidden" name="total" value="{{ $total }}" />


                    @if($bloqueado)
                        @php
                            $mensajes = [];

                            if ($totalExcede) {
                                $mensajes[] = "- El total del pedido excede los $10,000 pesos.";
                            }
                            if ($bloqueadoPorSaldo) {
                                $mensajes[] = "- Con este pedido, el adeudo superaría los $10,000 pesos.";
                            }
                            if ($bloqueadoPorHistorial) {
                                $mensajes[] = "- Tiene 2 o más créditos vencidos con saldo pendiente.";
                            }
                            if ($bloqueadoPorNivel) {
                                $mensajes[] = "- Su nivel de usuario es \"malo\".";
                            }
                        @endphp

                        @if(!empty($mensajes))
                            <div class="badge bg-mensaje">
                                @foreach($mensajes as $mensaje)
                                    <p style="margin: 0;">{{ $mensaje }}</p>
                                @endforeach
                                <p style="margin: 0;">Puedes cerrarlo como <strong>contado</strong>.</p>
                            </div>
                        @endif
                    @endif
                    <div class="pedido-actions">
                        <label for="metodo_pago_{{ $pedido->id_pedido }}">Método de pago:</label>
                        <select name="metodo_pago" required onchange="mostrarCreditos(this, {{ $pedido->id_pedido }})">
                            <option value="">-- Selecciona --</option>
                            <option value="contado">Contado</option>
                            @if(!$bloqueado)
                                <option value="credito">Crédito</option>
                            @endif
                        </select>

                        <div id="credito-opciones-{{ $pedido->id_pedido }}" style="display:none;">
                            @if(!$bloqueado)
                                <label>Seleccionar crédito:</label>
                                <select name="id_credito" class="form-select">
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
                                    <p class="badge bg-mensajes">
                                        - Tiene 3 créditos activos/vencidos.<br>Le recomendamos usar uno existente.
                                    </p>
                                @endif
                            @endif
                        </div>

                        <button type="submit" class="btn btn-primary">Cerrar pedido</button>
                    </div>
                </form>

            @elseif($pedidoCerrado)
                <p class="badge bg-cerrado"><strong>Pedido cerrado</strong></p>
            @endif

            <hr>
        @endforeach

        <!-- Paginación -->
        <div class="mt-4 d-flex justify-content-center">
            {{ $listaCarros->links('pagination::bootstrap-5') }}
        </div>
    @endif

    <br>
    <center><a href="{{ url('/carro') }}" class="btn botonlistado">Volver al listado</a></center>
</section>
<script>
    function mostrarCreditos(select, idPedido) {
        const div = document.getElementById('credito-opciones-' + idPedido);
        div.style.display = select.value === 'credito' ? 'block' : 'none';
    }
</script>
</main>
<x-footer/>
</div>
</body>
</html>
