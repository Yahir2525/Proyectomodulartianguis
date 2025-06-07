<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Principal de pedidos</title>
</head>
<body>
    <section>
        <div>
            <h1>Principal de pedidos</h1>
            <br>
            @if(Auth::check())
            @php
                $idPedido = request('id_pedido');
                $totalPedido = request('total');
            @endphp
            

            <a href="{{ url('/pedido/create') }}" class="button is-info is-fullwidth">
                Registrar un nuevo pedido
            </a><br><br>

            <form action="{{ url('/pedido/showPedido') }}" method="GET"> 
                <div class="sub">
                    <label for="id">ID de compra a buscar:</label>
                    <input type="text" id="id" name="id_pedido" placeholder="21" autofocus>
                </div><br><br>
                <input type="submit" id="enviar" name="enviar" value="Buscar">
            </form>

            @if($pedidoIndex->isNotEmpty())
                @php
                    // Agrupar carritos por id_pedido (por si no lo hiciste en el controlador)
                    $pedidosPorUsuario = $pedidoIndex->groupBy('id_user');
                @endphp
                @foreach($pedidosPorUsuario as $idUser => $pedidos)
                <br><h2>Tabla de pedidos registrados #{{ $idUser }}</h2>
                <center>
                    <table border="1">
                        <tr>
                            <th>ID pedido</th>
                            <th>Nombre usuario</th>
                            <th>Creditos del usuario</th>
                            <th>Total del pedido</th>
                            <th>Estado del pedido</th>
                            <th>Creado</th>
                            <th>Actualizado</th>
                            <th colspan="2">Acciones</th>
                        </tr>

                        @foreach ($pedidos as $pedido)
                            <tr>
                                <td>{{ $pedido->id_pedido }}</td>
                                <td>{{ optional($pedido->user)->nombre_usuario ?? 'Sin cliente' }}</td>
                                <td>{{}}
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
                                        <button type="submit" class="button is-danger">Eliminar Pedido</button>
                                    </form>
                                </td>
                                <form action="{{ route('credito.update', $idCredito) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="total" value="{{ $totalPedido }}">
                                    <button type="submit">Actualizar total y ver pedidos</button>
                                </form>
                            </tr>
                        @endforeach
                        @endforeach
                    </table>
                </center>
            @endif
            @endif
        </div>
    </section>
</body>
</html>
