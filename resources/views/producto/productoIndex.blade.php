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
            @if (Auth::check())
            <p>Sesión iniciada por: {{ Auth::user()->name }}</p>
            @else
            <p>No hay sesión activa.</p>
            @endif

            @can('create producto') <!-- Verifica si el usuario tiene el permiso de crear un usuario -->
            <a href="{{ url('/producto/create') }}" class="button is-info is-fullwidth">
                Registrar una nuevo producto
            </a><br><br>
            @else
            <p>No tienes permiso para crear un usuario.</p>
            @endcan
            <!-- <a href="{{ url('/producto/create') }}" class="button is-info is-fullwidth">
                Registrar una nueva compra
            </a><br><br> -->
            <form action="{{ url('/producto/showProducto') }}" method="GET"> 
                <div class="sub">
                    <label for="id">ID de compra a buscar:</label>
                    <input type="text" id="id" name="id_producto" placeholder="21" autofocus>
                </div><br><br>
                <input type="submit" id="enviar" name="enviar" value="buscar">
            </form>
            @if($productoIndex->isNotEmpty())
                <h2>Productos Registrados</h2>
                @php
                    $agrupadosPorTipo = $productoIndex->groupBy('tipo');
                @endphp
                @foreach ($agrupadosPorTipo as $tipo => $productos)
                    <h3>Tipo: {{ ucfirst($tipo) }}</h3>

                    <table border="1" cellpadding="5" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID producto</th>
                                <th>Nombre</th>
                                <th>Material</th>
                                <th>Color</th>
                                <th>Tamaño</th>
                                <th>Precio unitario</th>
                                <th>Piezas</th>
                                <th>Creado</th>
                                <th>Actualizado</th>
                                <th>Añadir al carrito</th>
                                <th>Editar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($productos as $producto)
                                <tr>
                                    <td>{{ $producto->id_producto }}</td>
                                    <td>{{ $producto->nombre }}</td>
                                    <td>{{ $producto->material }}</td>
                                    <td>{{ $producto->color }}</td>
                                    <td>{{ $producto->tamanio }}</td>
                                    <td>${{ number_format($producto->precio_unitario, 2) }}</td>
                                    <td>{{ number_format($producto->piezas) }}</td>
                                    <td>{{ $producto->created_at }}</td>
                                    <td>{{ $producto->updated_at }}</td>
                                    <td>
                                        <form action="{{ url('/carro', $producto->id_producto) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <button>Añadir</button>
                                        </form>
                                    </td>
                                    <td>
                                        <a href="{{ route('producto.edit', $producto->id_producto) }}">Editar</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <br>
                @endforeach
            @endif
        </div>
    </section>
</body>
</html>
