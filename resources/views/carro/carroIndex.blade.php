<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/carro/carroIndex.css') }}">
    <title>Principal de carros</title>
    @php
        use App\Models\CarroProducto;
    @endphp
</head>
<body>
    <div class="page-container">
        <main class="content">
        <br><x-barrageneral/>
            <section>
                <br><hr class="hr-grueso"><center><h1>Listado de carros</h1></center><hr class="hr-grueso">

                @if(Auth::check())
                    @can('create carro')
                        <br><p><a href="{{ url('/carro/create') }}" class="btn btn-registrar">Registrar un nuevo carro</a></p>
                    @endcan

                    <form action="{{ url('/carro/showCarro') }}" method="GET" class="buscar">
                        <label for="buscar">Buscar carro:</label>
                        <input type="text" id="buscar" name="buscar" placeholder="Ej. 21"
                        list="{{ Auth::user()->can('edit carro') ? 'usuarios' : '' }}"
                        value="{{ request('buscar') }}" />

                        @can('edit carro')
                        <datalist id="usuarios">
                            @foreach($usuarios as $usuario)
                                <option value="{{ $usuario->nombre_usuario }}"></option>
                            @endforeach
                        </datalist>
                        @endcan

                        <input type="submit" value="Buscar" class="btn btn-warning"/>

                    </form>

                    @if($carroIndex->isNotEmpty())
                        @php
                            $reservasGlobales = CarroProducto::select('id_producto')
                                ->selectRaw('SUM(cantidad) as total_reservado')
                                ->groupBy('id_producto')
                                ->pluck('total_reservado', 'id_producto');

                            $carrosPorPedido = $carroIndex->groupBy('id_pedido');
                        @endphp

                        @foreach($carrosPorPedido as $idPedido => $carros)
                            @php
                                $hayProductos = false;
                                foreach ($carros as $carrito) {
                                    if ($carrito->productos->isNotEmpty()) {
                                        $hayProductos = true;
                                        break;
                                    }
                                }
                                $pedido = $carros->first()->pedido;
                            @endphp

                            @if ($hayProductos)
                                <h2>Pedido #{{ $idPedido }} de {{ optional($carros->first()->user)->nombre_usuario ?? 'Sin cliente' }}</h2>

                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>ID carro</th>
                                                <th>Nombre</th>
                                                <th>Imagen</th>
                                                <th>Cantidad</th>
                                                <th>Precio unitario</th>
                                                <th>Subtotal</th>
                                                @if($pedido && $pedido->estado_pedido == 1)
                                                    <th>Editar</th>
                                                    <th>Eliminar producto</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $totalPedido = 0; @endphp
                                            @foreach ($carros as $carrito)
                                                @foreach ($carrito->productos as $producto)
                                                    @php
                                                        $stock = $producto->piezas;
                                                        $reservado = $reservasGlobales[$producto->id_producto] ?? 0;
                                                        $disponible = max(0, $stock - $reservado);
                                                        $subtotal = $producto->pivot->cantidad * $producto->precio_unitario;
                                                        $totalPedido += $subtotal;
                                                    @endphp
                                                    <tr>
                                                        <td data-label="ID">{{ $carrito->id_carro }}</td>
                                                        <td data-label="Nombre">{{ $producto->nombre }}</td>
                                                        <td data-label="Imagen">
                                                            @if (!empty($producto->imagen)) 
                                                                <img src="{{ Storage::disk('s3')->url($producto->imagen) }}" alt="Foto de producto" width="250">
                                                            @else
                                                                <span>Sin imagen</span>
                                                            @endif
                                                        </td>
                                                        <td data-label="Cantidad">{{ $producto->pivot->cantidad }}</td>
                                                        <td data-label="Precio">{{ $producto->precio_unitario }}</td>
                                                        <td data-label="Subtotal">{{ $subtotal }}</td>
                                                        @if($pedido && $pedido->estado_pedido == 1)
                                                            <td data-label="Editar">
                                                                <a href="{{ route('carro.edit', ['id_carro' => $carrito->id_carro, 'id_producto' => $producto->id_producto]) }}" class="btn btn-edit">
                                                                Editar</a>
                                                            </td>
                                                            <td data-label="Eliminar">
                                                                <form action="{{ route('carro.eliminarProducto', ['id_carro' => $carrito->id_carro, 'id_producto' => $producto->id_producto]) }}" method="POST">
                                                                @csrf
                                                                @method('DELETE')
                                                                    <button type="submit" class="btn btn-danger">Eliminar</button>
                                                                </form>
                                                            </td>
                                                        @endif
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <br><p><strong>Total del pedido #{{ $idPedido }}: {{ $totalPedido }}</strong></p>

                                @if($pedido && $pedido->estado_pedido == 1)
                                    @php
                                        $usuario = $carros->first()->user;

                                        $creditosTodos = \App\Models\Credito::where('id_user', $usuario->id_user)
                                            ->where('estado', 1)
                                            ->get();

                                        $creditosVigentes = $creditosTodos->filter(function($c) {
                                            return $c->fecha_vencimiento >= now();
                                        });

                                        $creditosVencidos = $creditosTodos->filter(function($c) {
                                            return $c->fecha_vencimiento < now();
                                        });

                                        $totalCreditos = $creditosTodos->sum('saldo_total');
                                        $totalExcede = $totalPedido > 10000;
                                        $sumaExcede = ($totalCreditos + $totalPedido) > 10000;

                                        $bloqueadoPorHistorial = $creditosVencidos->count() >= 2;

                                        $nivelUsuario = strtolower((string)($usuario->nivel_usuario ?? ''));
                                        $bloqueadoPorNivel = ($nivelUsuario === 'malo');

                                        $activosInclVencidos = $creditosTodos->count();
                                        $puedeCrearCredito = $activosInclVencidos < 3;

                                        $creditosDisponibles = $creditosVigentes->filter(function($c) use ($totalPedido) {
                                            return ($c->saldo_total + $totalPedido) <= 10000;
                                        });

                                        $bloqueado = $totalExcede || $sumaExcede || $bloqueadoPorHistorial || $bloqueadoPorNivel;
                                    @endphp
                                    <p>
                                        <form action="{{ route('carro.destroy', $carros->first()->id_carro) }}" method="POST" onsubmit="return confirm('¿Seguro que quieres eliminar todo este carro?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">Eliminar carro completo</button>
                                        </form>
                                    </p>

                                    <form action="{{ route('pedido.cerrar', $idPedido) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="total" value="{{ $totalPedido }}" />

                                        @if($bloqueado)
                                            @php
                                                $mensajes = [];

                                                if ($totalExcede) {
                                                    $mensajes[] = "- El total del pedido excede los $10,000 pesos.";
                                                }
                                                if ($sumaExcede) {
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
                                        <label for="metodo_pago_{{ $idPedido }}">Método de pago:</label>
                                        <select name="metodo_pago" required onchange="mostrarCreditos(this, {{ $idPedido }})">
                                            <option value="">-- Selecciona --</option>
                                            <option value="contado">Contado</option>
                                            @if(!$bloqueado)
                                                <option value="credito">Crédito</option>
                                            @endif
                                        </select>

                                        <div id="credito-opciones-{{ $idPedido }}" style="display:none;">
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
                                                    <p class="badge bg-mensajes">
                                                        - Tiene 3 créditos activos/vencidos.<br>Le recomendamos usar uno existente.
                                                    </p>
                                                @endif
                                            @endif
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">Cerrar pedido</button>

                                    </div>
                                    </form>
                                @else
                                    <p class="badge bg-cerrado">Pedido cerrado</p>
                                @endif
                                <hr>
                            @endif
                        @endforeach

                        <div class="mt-4 d-flex justify-content-center">
                            {{ $carroIndex->links('pagination::bootstrap-5') }}
                        </div>

                    @else
                        <p>No hay productos en el carrito.</p>
                    @endif
                @endif

            </section>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('form').forEach(form => {
                    const metodo = form.querySelector('[name="metodo_pago"]');
                    const idPedido = form.getAttribute('action')?.match(/(\d+)/)?.[0];
                    const creditoDiv = document.getElementById('credito-opciones-' + idPedido);

                    if (metodo && creditoDiv) {
                        metodo.addEventListener('change', () => {
                            creditoDiv.style.display = metodo.value === 'credito' ? 'block' : 'none';
                        });
                    }
                });
            });

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
