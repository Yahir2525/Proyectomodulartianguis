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
                                    <td>{{ $producto->id_producto }}</td>
                                    <td>{{ $producto->nombre }}</td>
                                    <td>
                                        @if (!empty($producto->imagen)) 
                                            <img src="{{ Storage::disk('s3')->url($producto->imagen) }}" alt="Foto de producto">
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
                                            <a href="{{ route('carro.edit', ['id_carro' => $carroItem->id_carro, 'id_producto' => $producto->id_producto]) }}" class="btn btn-edit">
                                                Editar
                                            </a>
                                        @else
                                            <span style="color: black;">Pedido cerrado</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(!$pedidoCerrado)
                                            <form action="{{ route('carro.eliminarProducto', ['id_carro' => $carroItem->id_carro, 'id_producto' => $producto->id_producto]) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar este producto?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger">Eliminar</button>
                                            </form>
                                        @else
                                            <span style="color: gray;">Pedido cerrado</span>
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
                    $bloqueadoPorHistorial = $creditosVencidos->count() >= 2;
                    $totalCreditosVigentes = $creditosVigentes->sum('saldo_total');
                    $bloqueadoPorSaldo = ($totalCreditosVigentes + $total) > 10000;
                    $nivelUsuario = strtolower((string)($usuario->nivel_usuario ?? ''));
                    $bloqueadoPorNivel = ($nivelUsuario === 'malo');
                    $puedeCrearCredito = ($creditosTodos->count() < 3);
                    $creditosDisponibles = $creditosVigentes->filter(fn($c) => ($c->saldo_total + $total) <= 10000);
                    $sinCreditosUsables = $creditosDisponibles->isEmpty() && !$puedeCrearCredito;
                    $bloqueado = $bloqueadoPorSaldo || $bloqueadoPorHistorial || $bloqueadoPorNivel || $sinCreditosUsables;
                @endphp

                <form action="{{ route('pedido.cerrar', $pedido->id_pedido) }}" method="POST">
                    @csrf
                    <input type="hidden" name="total" value="{{ $total }}" />


                    @if($bloqueado)
                        <p style="color:red;">
                            <strong>No puedes cerrar este pedido a crédito:</strong><br>
                            @if($bloqueadoPorSaldo) - La suma de créditos activos más este pedido excede los $10,000.<br>@endif
                            @if($bloqueadoPorHistorial) - El usuario tiene más de 2 créditos vencidos con saldo pendiente. No podrá cerrar pedidos a crédito.<br>@endif
                            @if($bloqueadoPorNivel) - El nivel del usuario es <strong>"malo"</strong>. No podrá cerrar pedidos a crédito.<br>@endif
                            @if($sinCreditosUsables) - No tienes créditos vigentes disponibles y no puedes crear uno nuevo.<br>@endif
                            <br>Puedes cerrarlo como <strong>contado</strong>.
                        </p>
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
                                    <p style="color:orange; font-style: italic;">
                                        - Ya tienes 3 créditos activos (incluye vencidos). No puedes crear uno nuevo, pero puedes usar los existentes vigentes.
                                    </p>
                                @endif
                            @endif
                        </div>

                        <button type="submit" class="btn btn-primary">Cerrar pedido</button>
                    </div>
                </form>

            @elseif($pedidoCerrado)
                <p style="color: gray;"><strong>Pedido cerrado</strong></p>
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
