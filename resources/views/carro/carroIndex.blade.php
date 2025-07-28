<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Principal de carros</title>

    @php
        use App\Models\CarroProducto;
    @endphp
</head>
<body>
<section>
    <div>
        <center><h1>CAJA REGISTRADORA</h1></center>

        @if(Auth::check())
            <p><a href="{{ url('/carro/create') }}">Registrar un nuevo carro</a></p>

            <form action="{{ url('/carro/showCarro') }}" method="GET">
                <label for="id_carro">ID de carro:</label>
                <input type="text" id="id_carro" name="id_carro" placeholder="21" />
                @can('edit carro')
                    <label for="nombre_usuario">Nombre de usuario:</label>
                    <input type="text" id="nombre_usuario" name="nombre_usuario" placeholder="carlitos" />
                @endcan
                <input type="submit" value="Buscar" />
            </form>

            <br /><br />

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
                        <h2>Pedido #{{ $idPedido }}</h2>

                        <table border="1" cellspacing="0" cellpadding="5">
                            <thead>
                                <tr>
                                    <th>ID del carrito</th>
                                    <th>ID del usuario</th>
                                    <th>Nombre del usuario</th>
                                    <th>ID del pedido</th>
                                    <th>ID del producto</th>
                                    <th>Nombre del producto</th>
                                    <th>Imagen</th>
                                    <th>Piezas disponibles</th>
                                    <th>Cantidad</th>
                                    <th>Precio unitario</th>
                                    <th>Subtotal</th>
                                    <th>Acciones</th>
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
                                            <td>{{ $carrito->id_carro }}</td>
                                            <td>{{ $carrito->id_user }}</td>
                                            <td>{{ optional($carrito->user)->nombre_usuario ?? 'Sin cliente' }}</td>
                                            <td>{{ $carrito->id_pedido }}</td>
                                            <td>{{ $producto->id_producto }}</td>
                                            <td>{{ $producto->nombre }}</td>
                                            <td>
                                                @if ($producto->imagen)
                                                    <img src="{{ asset($producto->imagen) }}" alt="Imagen del producto" width="250">
                                                @else
                                                    Sin imagen
                                                @endif
                                            </td>
                                            <td>{{ $disponible }}</td>
                                            <td>{{ $producto->pivot->cantidad }}</td>
                                            <td>{{ $producto->precio_unitario }}</td>
                                            <td>{{ $subtotal }}</td>
                                            <td>
                                                @if($pedido && $pedido->estado_pedido == 1)
                                                    <a href="{{ route('carro.edit', ['id_carro' => $carrito->id_carro, 'id_producto' => $producto->id_producto]) }}">
                                                        <button type="button">Editar</button>
                                                    </a>
                                                    <form action="{{ route('carro.eliminarProducto', ['id_carro' => $carrito->id_carro, 'id_producto' => $producto->id_producto]) }}" method="POST" onsubmit="return confirm('¿Estás seguro?');">
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
                                @endforeach
                            </tbody>
                        </table>

                        <p><strong>Total del pedido #{{ $idPedido }}: {{ $totalPedido }}</strong></p>

                        @if($pedido && $pedido->estado_pedido == 1)
                            @php
                                $usuario = $carros->first()->user;
                                $creditosActivos = \App\Models\Credito::where('id_user', $usuario->id_user)
                                    ->where('estado', 1)
                                    ->whereDate('fecha_vencimiento', '>=', now())
                                    ->get();

                                $totalCreditos = $creditosActivos->sum('saldo_total');
                                $pedidoExcede = $totalPedido > 10000;
                                $sumaExcede = ($totalCreditos + $totalPedido) > 10000;

                                $bloqueado = $pedidoExcede || $sumaExcede;

                                $creditosDisponibles = \App\Models\Credito::where('id_user', $usuario->id_user)
                                    ->where('estado', 1)
                                    ->whereDate('fecha_vencimiento', '>=', now())
                                    ->get();
                            @endphp

                            <form action="{{ route('pedido.cerrar', $idPedido) }}" method="POST">
                                @csrf
                                <input type="hidden" name="total" value="{{ $totalPedido }}" />

                                @if($bloqueado)
                                    <p style="color:red;">
                                        <strong>No puedes cerrar este pedido a crédito:</strong><br>
                                        @if($pedidoExcede)
                                            - El total del pedido supera los $10,000.<br>
                                        @endif
                                        @if($sumaExcede)
                                            - La suma del saldo de los créditos activos más este pedido supera los $10,000.<br>
                                        @endif
                                        Puedes elegir "Contado" para cerrar sin restricciones.
                                    </p>
                                @endif

                                <label for="metodo_pago_{{ $idPedido }}">Método de pago:</label>
                                <select name="metodo_pago" required>
                                    <option value="">-- Selecciona --</option>
                                    <option value="contado">Contado</option>
                                    @if(!$bloqueado)
                                        <option value="credito">Crédito</option>
                                    @endif
                                </select>

                                <div class="credito-opciones" style="display:none;">
                                    @if($creditosDisponibles->isNotEmpty())
                                        <label>Seleccionar crédito:</label>
                                        <select name="id_credito" class="select-credito">
                                            <option value="">-- Crear nuevo crédito --</option>
                                            @foreach($creditosDisponibles as $credito)
                                                <option value="{{ $credito->id_credito }}">
                                                    Crédito #{{ $credito->id_credito }} - Saldo: {{ $credito->saldo_total }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @else
                                        <input type="hidden" name="id_credito" value="">
                                    @endif
                                </div>

                                <button type="submit" style="margin-top: 8px;">Cerrar pedido</button>
                            </form>
                        @else
                            <p style="color: gray;"><strong>Pedido cerrado</strong></p>
                        @endif

                        <hr>
                    @endif
                @endforeach
            @else
                <p>No hay productos en el carrito.</p>
            @endif
        @endif
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form').forEach(form => {
        const metodo = form.querySelector('[name="metodo_pago"]');
        const creditoOpciones = form.querySelector('.credito-opciones');

        if (metodo && creditoOpciones) {
            metodo.addEventListener('change', () => {
                if (metodo.value === 'credito') {
                    creditoOpciones.style.display = 'block';
                } else {
                    creditoOpciones.style.display = 'none';
                }
            });
        }
    });
});
</script>

</body>
</html>
