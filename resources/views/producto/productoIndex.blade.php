<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Principal de productos</title>
</head>
<body>
    <section>
        <div>
            <h1>Principal de productos</h1>
            <br>
            <a href="{{ url('/producto/create') }}" class="button is-info is-fullwidth">
                Registrar una nueva compra
            </a><br><br>
            <form action="{{ url('/producto/showProducto') }}" method="GET"> 
                <div class="sub">
                    <label for="id">ID de compra a buscar:</label>
                    <input type="text" id="id" name="id_producto" placeholder="21" autofocus>
                </div><br><br>
                <input type="submit" id="enviar" name="enviar" value="buscar">
            </form>
            @if($productoIndex->isNotEmpty())
                <br><h2>Tablas de productos registrados</h2>
                @foreach ($productoIndex as $producto)
                    <center>
                        <table>
                            <tr>
                                <th colspan="2">Tabla del producto: {{ $producto->id_producto }}</th>
                            </tr>
                            <tr>
                                <th>Atributo</th>
                                <th>Valor</th>
                            </tr>
                            <tr>
                                <td>ID del producto</td>
                                <td>{{ $producto->id_producto }}</td>
                            </tr>
                            <tr>
                                <td>Nombre</td>
                                <td>{{ $producto->nombre }}</td>
                            </tr>
                            <tr>
                                <td>Material</td>
                                <td>{{ $producto->material}}</td>
                            </tr>
                            <tr>
                                <td>Color</td>
                                <td>{{ $producto->color }}</td>
                            </tr>
                            <tr>
                                <td>Tamaño</td>
                                <td>{{ $producto->tamanio }}</td>
                            </tr>
                            <tr>
                                <td>Precio unitario</td>
                                <td>{{ number_format($producto->precio_unitario, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Piezas</td>
                                <td>{{ number_format($producto->piezas) }}</td>
                            </tr>
                            <tr>
                                <td>Creado</td>
                                <td>{{ $producto->created_at }}</td>
                            </tr>
                            <tr>
                                <td>Actualizado</td>
                                <td>{{ $producto->updated_at }}</td>
                            </tr>
                        </table>
                        <br>
                        <a href="{{ route('producto.edit', $producto->id_producto) }}" class="button is-primary">Editar Compra</a>
                    </center>
                @endforeach 
            @endif
        </div>
    </section>
</body>
</html>
