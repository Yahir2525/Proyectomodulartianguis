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
                <div class="sub">
                    <label for="id">ID de compra a buscar:</label>
                    <input type="text" id="id" name="id_credito" placeholder="21" autofocus>
                </div><br><br>
                <input type="submit" id="enviar" name="enviar" value="buscar">
            </form>
            @if($creditoIndex->isNotEmpty())
    <br><h2>Tablas de créditos registrados</h2>
    <center>
        <table border="1">
            <tr>
                <th>ID del crédito</th>
                <th>Nombre del Cliente</th>
                <th>Saldo Total</th>
                <th>Total Abonado</th>
                <th>Fecha de Liquidación</th>
                <th>Fecha de Vencimiento</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
            @foreach ($creditoIndex as $credito)
                <tr>
                    <td>{{ $credito->id_credito }}</td>
                    <td>{{ optional($credito->compra)->nombre_usuario ?? 'Sin cliente' }}</td>
                    <td>{{ number_format($credito->saldo_total, 2) }}</td>
                    <td>{{ number_format($credito->total_abonado, 2) }}</td>
                    <td>{{ $credito->fecha_liquidacion }}</td>
                    <td>{{ $credito->fecha_vencimiento }}</td>
                    <td>{{ $credito->estado ? 'Activo' : 'Inactivo' }}</td>
                    <td>
                        <a href="{{ route('credito.edit', $credito->id_credito) }}" class="button is-primary">Editar</a>
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
