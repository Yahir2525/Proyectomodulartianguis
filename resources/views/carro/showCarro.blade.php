<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de Carro</title>
</head>
<body>
    <h1>Carro #{{ $carro->id_carro }}</h1>

    <p><strong>Usuario:</strong> {{ optional($carro->user)->nombre_usuario ?? 'Sin usuario' }}</p>

    @if ($carro->productos->isEmpty())
        <p>Este carro no tiene productos.</p>
    @else
        @php
            // Calcular reservas globales por producto (excluyendo este carro)
            $reservasGlobales = \App\Models\CarroProducto::select('id_producto')
                ->selectRaw('SUM(cantidad) as reservadas')
                ->where('id_carro', '!=', $carro->id_carro)
                ->groupBy('id_producto')
                ->pluck('reservadas', 'id_producto');
        @endphp

        <table border="1" cellpadding="6">
            <thead>
                <tr>
                    <th>ID Carro</th>
                    <th>ID Usuario</th>
                    <th>Nombre de usuario</th>
                    <th>ID Producto</th>
                    <th>Nombre del Producto</th>
                    <th>Imagen</th>
                    <th>Piezas disponibles</th>
                    <th>Cantidad</th>
                    <th>Precio unitario</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @php $total = 0; @endphp
                @foreach ($carro->productos as $producto)
                    @php
                        $id = $producto->id_producto;
                        $stockOriginal = $producto->piezas;
                        $reservado = $reservasGlobales[$id] ?? 0;
                        $piezas_disponibles = max(0, $stockOriginal - $reservado);

                        $cantidad = $producto->pivot->cantidad;
                        $subtotal = $cantidad * $producto->precio_unitario;
                        $total += $subtotal;
                    @endphp
                    <tr>
                        <td>{{ $carro->id_carro }}</td>
                        <td>{{ $carro->id_user }}</td>
                        <td>{{ optional($carro->user)->nombre_usuario ?? 'Sin cliente' }}</td>
                        <td>{{ $producto->id_producto }}</td>
                        <td>{{ $producto->nombre }}</td>
                        <td>
                            @if ($producto->imagen)
                                <img src="{{ asset($producto->imagen) }}" alt="Imagen del producto" width="250">
                            @else
                                Sin imagen
                            @endif
                        </td>
                        <td>{{ $piezas_disponibles }}</td>
                        <td>{{ $cantidad }}</td>
                        <td>{{ $producto->precio_unitario }}</td>
                        <td>{{ $subtotal }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <p><strong>Total del carrito:</strong> {{ $total }}</p>
    @endif

    <br>
    <a href="{{ url('/carro') }}">Volver al listado</a>
</body>
</html>
