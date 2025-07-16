<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle del Producto</title>
</head>
<body>
    <h1>Producto #{{ $producto->id_producto }}</h1>

    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    @if(session('error'))
        <p style="color: red;">{{ session('error') }}</p>
    @endif

    @if($producto)
        <table border="1" cellpadding="6">
            <thead>
                <tr>
                    <th>ID Producto</th>
                    <th>Nombre</th>
                    <th>Imagen</th>
                    <th>Tipo</th>
                    <th>Material</th>
                    <th>Color</th>
                    <th>Tamaño</th>
                    <th>Marca</th>
                    <th>Precio Unitario</th>
                    <th>Piezas Disponibles</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $producto->id_producto }}</td>
                    <td>{{ $producto->nombre }}</td>
                    <p>Ruta imagen: {{ $producto->imagen }}</p>
                    <img src="{{ asset($producto->imagen) }}" alt="Imagen del producto" width="250">
                    <td>
                        @if ($producto->imagen)
                            <img src="{{ asset($producto->imagen) }}" alt="Imagen del producto" width="100">
                        @else
                            Sin imagen
                        @endif
                    </td>
                    <td>{{ $producto->tipo }}</td>
                    <td>{{ $producto->material }}</td>
                    <td>{{ $producto->color }}</td>
                    <td>{{ $producto->tamanio }}</td>
                    <td>{{ $producto->marca }}</td>
                    <td>${{ number_format($producto->precio_unitario, 2) }}</td>
                    <td>{{ $producto->piezas }}</td>
                </tr>
            </tbody>
        </table>

        <br>
        <a href="{{ route('producto.edit', $producto->id_producto) }}">Editar producto</a>
    @else
        <p>Producto no encontrado.</p>
    @endif

    <br>
    <a href="{{ route('producto.index') }}">Volver al listado</a>
</body>
</html>
