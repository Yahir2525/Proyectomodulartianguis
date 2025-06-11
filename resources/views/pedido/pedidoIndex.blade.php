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
            <h1>Principal de pedidos</h1><br>
            @if(Auth::check())
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
                        $pedidosPorCredito = $pedidoIndex->groupBy('id_credito');
                    @endphp
                    @foreach($pedidosPorCredito as $idCredito => $pedidos)
                        <br><h2>Pedidos del usuario #{{ $idCredito }}</h2>
                        <center>
                            <table border="1">
                                <tr>
                                    <th>ID pedido</th>
                                    <th>Nombre usuario</th>
                                    <th>Total del pedido</th>
                                    <th>Créditos del usuario</th>
                                    <th>Acción</th>
                                    
                                    <th>Estado del pedido</th>
                                    <th>Creado</th>
                                    <th>Actualizado</th>
                                    <th>Editar</th>
                                    <th>Eliminar</th>
                                </tr>
                                @foreach ($pedidos as $pedido)
                                    <tr>
                                        <td>{{ $pedido->id_pedido }}</td>
                                        <td>{{ optional($pedido->user)->nombre_usuario ?? 'Sin cliente' }}</td>
                                        <td>{{$idCredito}}</td>
                                            <form action="{{ route('credito.update', $idCredito) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="total" value="{{ $pedido->total_pedido }}">
                                                <button type="submit">Asignar / Cambiar crédito</button>
                                            </form>
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
                                                <button type="submit" class="button is-danger">Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </center>
                    @endforeach
                @endif
            @endif
        </div>
    </section>
</body>
</html>
