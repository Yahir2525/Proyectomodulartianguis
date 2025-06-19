<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Principal de detalle carro</title>
</head>
<body>
    <section>
        <div>
            <h1>Principal de detalle</h1>
            @if(Auth::check())
            <p>
                <a href="{{ url('/detalle/create') }}">Registrar un nuevo carro</a>
            </p>

            <form action="{{ url('/detalle/showDetalle') }}" method="GET">
                <label for="id">ID de detalle a buscar:</label>
                <input type="text" id="id" name="id_detalle" placeholder="21" autofocus>
                <input type="submit" value="Buscar">
            </form>
            <br><br>
            @if($detalleIndex->isNotEmpty())
                @php
                    $detallePorPedido = $detalleIndex->groupBy('id_pedido');
                @endphp

                @foreach($detallePorPedido as $idPedido => $detalles)
                    <h2>Numero de pedido #{{ $idPedido }}</h2>

                    <table border="1" cellspacing="0" cellpadding="5">
                        <thead>
                            <tr>
                                <th>ID del detalle</th>
                                <th>Nombre del usuario</th>
                                <th>ID del pedido</th>
                                <th>Total</th>
                                <th>Estado del detalle</th>
                                <th>Fecha de creación</th>
                                <th>Última actualización</th>
                                <th>Editar</th>
                                <th>Eliminar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($detalles as $detalle)
                            <tr>
                                <td>{{ $detalle->id_detalle }}</td>
                                <td>{{ optional($detalle->user)->nombre_usuario ?? 'Sin cliente' }}</td>
                                <td>{{ $detalle->id_pedido }}</td>
                                <td>{{ $detalle->total_carro }}</td>
                                <td>{{ $detalle->estado_carro }}</td>
                                <td>{{ $detalle->created_at }}</td>
                                <td>{{ $detalle->updated_at }}</td>
                                <td>
                                    <a href="{{ route('detalle.edit', $detalle->id_detalle) }}?total={{ $detalle->total_carro }}">Editar</a>
                                </td>
                                <td>
                                    <form action="{{ url('/detalle', $detalle->id_detalle) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit">Eliminar</button>
                                    </form>
                                </td>
                                <td>
                                    @if(!$detalle->id_pedido)
                                        <form action="{{ route('detalle.crearDesdeDetalle', $detalle->id_detalle) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="nuevo_pedido" value="1">
                                            <input type="hidden" name="total" value="{{ $detalle->total_carro }}">
                                            <input type="hidden" name="id_user" value="{{ $detalle->id_user }}">
                                            <button type="submit">Crear pedido</button>
                                        </form>
                                    @else
                                        <form action="{{ route('detalle.update', $detalle->id_detalle) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="total" value="{{ $detalle->total_carro }}">
                                            <button type="submit">Actualizar detalle</button>
                                        </form>
                                    @endif
                                </td>
                                <!-- Falta mandar total al pedido -->
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <hr>
                @endforeach
            @endif
            @endif
        </div>
    </section>
</body>
</html>
