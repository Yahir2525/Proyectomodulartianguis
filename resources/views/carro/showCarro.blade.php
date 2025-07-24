<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle(s) de Carro</title>
    <style>
        table { border-collapse: collapse; width: 100%; margin-bottom: 30px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: center; }
        th { background-color: #eee; }
        img { max-width: 120px; height: auto; }
        h2 { margin-top: 40px; }
    </style>
</head>
<body>
    <h1>Detalle(s) de Carro</h1>

    @php
        // Normaliza la variable para trabajar siempre con un array de carros
        $listaCarros = isset($carros) ? $carros : (isset($carro) ? collect([$carro]) : collect([]));
    @endphp

    @if($listaCarros->isEmpty())
        <p>No se encontraron carros para mostrar.</p>
    @else
        @foreach ($listaCarros as $carroItem)
            @php
                $reservasGlobales = \App\Models\CarroProducto::select('id_producto')
                    ->selectRaw('SUM(cantidad) as reservadas')
                    ->where('id_carro', '!=', $carroItem->id_carro)
                    ->groupBy('id_producto')
                    ->pluck('reservadas', 'id_producto');

                $total = 0;
            @endphp

            <h2>Carro #{{ $carroItem->id_carro }}</h2>

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
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($carroItem->productos as $producto)
                            @php
                                $reservado = $reservasGlobales[$producto->id_producto] ?? 0;
                                $disponibles = max(0, $producto->piezas - $reservado);
                                $cantidad = $producto->pivot->cantidad;
                                $subtotal = $cantidad * $producto->precio_unitario;
                                $total += $subtotal;
                            @endphp
                            <tr>
                                <td>{{ $producto->id_producto }}</td>
                                <td>{{ $producto->nombre }}</td>
                                <td>
                                    @if ($producto->imagen)
                                        <img src="{{ asset($producto->imagen) }}" alt="Imagen">
                                    @else
                                        Sin imagen
                                    @endif
                                </td>
                                <td>{{ $producto->material }}</td>
                                <td>{{ $producto->color }}</td>
                                <td>{{ $producto->tamanio }}</td>
                                <td>{{ $disponibles }}</td>
                                <td>{{ $cantidad }}</td>
                                <td>${{ number_format($producto->precio_unitario, 2) }}</td>
                                <td>${{ number_format($subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <p><strong>Total del carrito:</strong> ${{ number_format($total, 2) }}</p>
            @endif
        @endforeach
    @endif

    <br>
    <a href="{{ url('/carro') }}">Volver al listado</a>
</body>
</html>
