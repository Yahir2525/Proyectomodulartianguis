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
                        $pedidosPorUsuario = $pedidoIndex->groupBy('id_user');
                    @endphp

                    @foreach($pedidosPorUsuario as $idUser => $pedidos)
                        <br><h2>Pedidos del usuario #{{ $idUser }}</h2>
                        <center>
                            <table border="1">
                                <tr>
                                    <th>ID pedido</th>
                                    <th>Nombre usuario</th>
                                    <th>Créditos del usuario</th>
                                    <th>Acción</th>
                                    <th>Total del pedido</th>
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
                                        <td>
                                        @foreach($creditos[$pedido->id_user] ?? [] as $credito)
                                            <form action="{{ url('credito.update'. $pedido->id_credito) }}" method="POST">
                                                @csrf
                                                @method('PUT')

                                                <select name="id_credito" required>
                                                        <option value="{{ $credito->id_credito }}"
                                                            @if($pedido->id_credito == $credito->id_credito) selected @endif>
                                                            Crédito #{{ $credito->id_credito }} - ${{ $credito->saldo_total }}
                                                        </option>
                                                </select>

                                                <input type="hidden" name="total" value="{{ $pedido->total_pedido }}">
                                                
                                        </td>
                                        <td>
                                                <button type="submit">Asignar / Cambiar crédito</button>
                                            </form>
                                            @endforeach
                                        </td>
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
