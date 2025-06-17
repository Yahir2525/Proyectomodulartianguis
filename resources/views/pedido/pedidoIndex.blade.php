<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Principal de pedidos</title>
</head>
<body>
    <h1>Principal de pedidos</h1>

    @if(Auth::check())
        <p>
            <a href="{{ url('/pedido/create') }}">Registrar un nuevo pedido</a>
        </p>

        <form action="{{ url('/pedido/showPedido') }}" method="GET">
            <label for="id">ID de compra a buscar:</label>
            <input type="text" id="id" name="id_pedido" placeholder="21" autofocus>
            <input type="submit" value="Buscar">
        </form>

        @if($pedidoIndex->isNotEmpty())
            @php
                $pedidosPorCredito = $pedidoIndex->groupBy('id_credito');
            @endphp

            @foreach($pedidosPorCredito as $idCredito => $pedidos)
                <h2>Pedidos del usuario #{{ $idCredito ?? 'Sin crédito' }}</h2>

                <table border="1" cellpadding="5" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID pedido</th>
                            <th>Nombre usuario</th>
                            <th>ID crédito</th>
                            <th>Total del pedido</th>
                            <th>Estado</th>
                            <th>Creado</th>
                            <th>Actualizado</th>
                            <th>Editar</th>
                            <th>Eliminar</th>
                            <th>Crédito</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pedidos as $pedido)
                            <tr>
                                <td>{{ $pedido->id_pedido }}</td>
                                <td>{{ optional($pedido->user)->nombre_usuario ?? 'Sin cliente' }}</td>
                                <td>{{ $idCredito ?? 'N/A' }}</td>
                                <td>{{ $pedido->total_pedido }}</td>
                                <td>{{ $pedido->estado_pedido }}</td>
                                <td>{{ $pedido->created_at }}</td>
                                <td>{{ $pedido->updated_at }}</td>
                                <td>
                                    <a href="{{ route('pedido.edit', $pedido->id_pedido) }}?total={{ $pedido->total_pedido }}">Editar</a>
                                </td>
                                <td>
                                    <form action="{{ url('/pedido', $pedido->id_pedido) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit">Eliminar</button>
                                    </form>
                                </td>
                                <td>
                                    @if(!$pedido->id_credito)
                                        <form action="{{ route('credito.crearDesdePedido', $pedido->id_pedido) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="nuevo_credito" value="1">
                                            <input type="hidden" name="total" value="{{ $pedido->total_pedido }}">
                                            <input type="hidden" name="id_user" value="{{ $pedido->id_user }}">
                                            <input type="date" name="fecha_liquidacion" required>
                                            <input type="date" name="fecha_vencimiento" required>
                                            <button type="submit">Crear crédito</button>
                                        </form>
                                    @else
                                        <form action="{{ route('credito.update', $pedido->id_credito) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="total" value="{{ $pedido->total_pedido }}">
                                            <button type="submit">Actualizar crédito</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endforeach
        @endif
    @endif
</body>
</html>
