<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Principal de productos</title>
    <style>
        .sin-stock {
            background-color: #ffe5e5;
        }
        .resaltado {
            font-weight: bold;
            color: red;
        }
        .cant-input {
            width: 60px;
        }
    </style>

    @php use App\Models\CarroProducto; @endphp
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

            @can('create producto')
                <a href="{{ url('/producto/create') }}">Registrar un nuevo producto</a>
            @endcan
            <hr><br>
            <form action="{{ url('/carro/agregar-multiples') }}" method="POST">
                @csrf
                
                @if($productoIndex->isNotEmpty())
                    @php $agrupadosPorTipo = $productoIndex->groupBy('tipo'); @endphp
                    @foreach ($agrupadosPorTipo as $tipo => $productos)
                        <h3>Tipo: {{ ucfirst($tipo) }}</h3>
                        <table border="1" cellpadding="5" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Seleccionar</th>
                                    <th>ID producto</th>
                                    <th>Nombre</th>
                                    <th>Material</th>
                                    <th>Color</th>
                                    <th>Tamaño</th>
                                    <th>Precio unitario</th>
                                    <th>Piezas disponibles</th>
                                    <th>Cantidad</th>
                                    <th>Editar</th>
                                </tr>
                            </thead>
                            <tbody>
                                    @foreach ($productos as $producto)
                                        @php
                                            $reservadas = CarroProducto::where('id_producto', $producto->id_producto)->sum('cantidad');
                                            $disponibles = max(0, $producto->piezas - $reservadas);
                                        @endphp
                                        <tr class="{{ $disponibles == 0 ? 'sin-stock' : '' }}">
                                            <td>
                                            <input type="checkbox" name="productos_seleccionados[]" value="{{ $producto->id_producto }}" {{ $disponibles == 0 ? 'disabled' : '' }}>
                                            </td>
                                            
                                            <td>{{ $producto->id_producto }}</td>
                                            <td>{{ $producto->nombre }}</td>
                                            <td>{{ $producto->material }}</td>
                                            <td>{{ $producto->color }}</td>
                                            <td>{{ $producto->tamanio }}</td>
                                            <td>${{ number_format($producto->precio_unitario, 2) }}</td>
                                            <td class="{{ $disponibles == 0 ? 'resaltado' : '' }}">
                                                {{ $disponibles }}
                                            </td>
                                            <td>
                                                <input type="number" name="cantidades[{{ $producto->id_producto }}]" min="1" max="{{ $disponibles }}" class="cant-input" {{ $disponibles == 0 ? 'disabled' : '' }}>
                                            </td>
                                            <td><a href="{{ route('producto.edit', $producto->id_producto) }}">Editar</a></td>
                                        </tr>
                                    @endforeach
                            </tbody>
                        </table>
                        <br>
                    @endforeach

                    <label for="id_pedido">Selecciona un pedido existente:</label>
                    <select name="id_pedido" required>
                        <option value="">-- Selecciona --</option>
                        @foreach($pedidosUsuario as $pedido)
                            <option value="{{ $pedido->id_pedido }}">Pedido #{{ $pedido->id_pedido }}</option>
                        @endforeach
                    </select>
                    <br><br>

                    <input type="hidden" name="id_user" value="{{ Auth::id() }}">
                    <button type="submit">Agregar seleccionados al carrito</button>
                @else
                    <p>No hay productos registrados.</p>
                @endif
            </form>
        </div>
    </section>
</body>
</html>
