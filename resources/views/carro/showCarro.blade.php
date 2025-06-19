<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de Carro</title>
</head>
<body>
    <h1>Carro #{{ $carro->id_carro }}</h1>

    <p><strong>Usuario:</strong> {{ optional($carro->user)->nombre_usuario ?? 'Sin usuario' }}</p>
    <p><strong>ID Detalle:</strong> {{ $carro->id_detalle }}</p>

    @if ($carro->productos->isEmpty())
        <p>Este carro no tiene productos.</p>
    @else
        <table border="1" cellpadding="6">
            <thead>
                <tr>
                    <th>ID Carro</th>
                    <th>ID Usuario</th>
                    <th>Nombre de usuario</th>
                    <th>ID detalle</th>
                    <th>ID Producto</th>
                    <th>Nombre del Producto</th>
                    <th>Piezas disponibles</th>
                    <th>Cantidad</th>
                    <th>Precio unitario</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $total = 0;
                    $reservasAcumuladas = [];
                @endphp
                @foreach ($carro->productos as $producto)
                    @php
                        $id = $producto->id_producto;
                        $stockOriginal = $producto->piezas;

                        $reservadoAntes = $reservasAcumuladas[$id] ?? 0;
                        $piezas_disponibles = $stockOriginal - $reservadoAntes;

                        $reservasAcumuladas[$id] = $reservadoAntes + $producto->pivot->cantidad;

                        $subtotal = $producto->pivot->cantidad * $producto->precio_unitario;
                    @endphp
                    <tr>
                        <td>{{ $carro->id_carro }}</td>
                        <td>{{ $carro->id_user }}</td>
                        <td>{{ optional($carro->user)->nombre_usuario ?? 'Sin cliente' }}</td>
                        <td>{{ $carro->id_detalle}}</td>
                        <td>{{ $producto->id_producto }}</td>
                        <td>{{ $producto->nombre }}</td>
                        <td>{{ $piezas_disponibles }}</td>
                        <td>{{ $producto->pivot->cantidad }}</td>
                        <td>{{ $producto->precio_unitario }}</td>
                        <td>{{ $subtotal }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <br>
    <a href="{{ url('/carro') }}">Volver al listado</a>
</body>
</html>
