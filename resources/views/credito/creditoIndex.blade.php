<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Principal de creditos</title>
</head>
<body>
    <section>
        <div>
            <h1>Principal de creditos</h1>
            <br>
            <a href="{{ url('/credito/create') }}" class="button is-info is-fullwidth">
                Registrar una nuevo credito
            </a><br><br>
            <form action="{{ url('/credito/showCredito') }}" method="GET">
                <label for="id_pedido">Buscar por ID de pedido:</label>
                <input type="text" id="id_credito" name="id_credito" placeholder="Ej. 21" autofocus/>
                @can('edit credito')
                <label for="nombre_usuario">o por nombre de usuario:</label>
                <input type="text" id="nombre_usuario" name="nombre_usuario" placeholder="Ej. Pepito" />
                @endcan
                <input type="submit" value="Buscar" />
            </form>


            @if($creditoIndex->isNotEmpty())
            <br><h2>Tablas de créditos registrados</h2>
            <center>
                <table border="1">
                    <tr>
                        <th>ID del crédito</th>
                        <th>Nombre del Cliente</th>
                        <th>Fecha de Liquidación</th>
                        <th>Fecha de Vencimiento</th>
                        <th>Estado</th>
                        <th>Saldo Total</th>
                        <th>Acciones</th>
                        <th>Eliminar</th>
                    </tr>
                    @foreach ($creditoIndex as $credito)
                        <tr>
                            <td>{{ $credito->id_credito }}</td>
                            <td>{{ optional($credito->user)->nombre_usuario ?? 'Sin cliente' }}</td>
                            <td>{{ $credito->fecha_liquidacion }}</td>
                            <td>{{ $credito->fecha_vencimiento }}</td>
                            <td>{{ $credito->estado ? 'Activo' : 'Inactivo' }}</td>
                            <td>{{ $credito->saldo_total ?? 'Sin adeudo'}}</td>
                            <td>
                                <a href="{{ route('credito.edit', $credito->id_credito) }}" class="button is-primary">Editar</a>
                            </td>
                            <td>
                                    <form action="{{ url('/credito', $credito->id_credito) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit">Eliminar</button>
                                    </form>
                            </td>
                        </tr>
                    @endforeach
                </table>
            </center>
        @endif
        </div>
    </section>
</body>
</html>
