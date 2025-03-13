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
                <br><h2>Tablas de creditos registrados</h2>
                @foreach ($creditoIndex as $credito)
                    <center>
                        <table>
                            <tr>
                                <th colspan="2">Tabla del pedido: {{ $credito->id_credito }}</th>
                            </tr>
                            <tr>
                                <th>Atributo</th>
                                <th>Valor</th>
                            </tr>
                            <tr>
                                <td>ID del credito</td>
                                <td>{{ $credito->id_credito }}</td>
                            </tr>
                            <tr>
                            <td>Nombre del Cliente</td>
                            <td>{{ optional($credito->cliente)->nombre_usuario ?? 'Sin cliente' }}</td>
                            </tr>
                            <tr>
                                <td>ID de la compra</td>
                                <td>{{ optional($credito->compra)->id_compra ?? 'Sin compra' }}</td>
                            </tr>
                            <tr>
                                <td>Fecha de liquidación</td>
                                <td>{{ $credito->fecha_liquidacion }}</td>
                            </tr>
                            <tr>
                                <td>Fecha de vencimiento</td>
                                <td>{{ $credito->fecha_vencimiento }}</td>
                            </tr>
                            <tr>
                                <td>Estado</td>
                                <td>{{ $credito->estado ? 'Activo' : 'Inactivo' }}</td>
                            </tr>
                            <tr>
                                <td>Saldo inicial</td>
                                <td>{{ number_format($credito->saldo_inicial, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Total abonado</td>
                                <td>{{ number_format($credito->total_abonado, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Saldo pendiente</td>
                                <td>{{ number_format($credito->saldo_pendiente, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Creado</td>
                                <td>{{ $credito->created_at }}</td>
                            </tr>
                            <tr>
                                <td>Actualizado</td>
                                <td>{{ $credito->updated_at }}</td>
                            </tr>
                        </table>
                        <br>
                        <a href="{{ route('credito.edit', $credito->id_credito) }}" class="button is-primary">Editar Compra</a>
                    </center>
                @endforeach 
            @endif
        </div>
    </section>
</body>
</html>
