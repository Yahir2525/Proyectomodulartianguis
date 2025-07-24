<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Principal de abonos</title>
</head>
<body>
    <section>
        <h1>Principal de abonos</h1><br>
        @can('create abono')
            <a href="{{ url('/abono/create') }}">Registrar nuevo abono</a>
        @endcan
        <br><br>
        @can('view abono')
        <form action="{{ url('/abono/showAbono') }}" method="GET">
            <label for="id">ID de compra a buscar:</label>
            <input type="text" id="id" name="id_abono" placeholder="21" autofocus>
            <input type="submit" name="enviar" value="Buscar">
        </form>
        @endcan

        @if($abonoIndex->isNotEmpty())
            <h2>Tabla de abonos registrados</h2>
            <table border="1" cellspacing="0" cellpadding="5">
                <thead>
                    <tr>
                        <th>ID del abono</th>
                        <th>ID del crédito</th>
                        <th>Nombre de usuario</th>
                        <th>Monto</th>
                        <th>Fecha de creación</th>
                        <th>Última actualización</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($abonoIndex as $abono)
                    <tr>
                        <td>{{ $abono->id_abono }}</td>
                        <td>{{ optional($abono->credito)->id_credito ?? 'Sin crédito' }}</td>
                        <td>{{ optional($abono->user)->nombre_usuario ?? 'No tiene usuario' }}</td>
                        <td>{{ $abono->monto_abono }}</td>
                        <td>{{ $abono->created_at }}</td>
                        <td>{{ $abono->updated_at }}</td>
                        <td>
                            @can('edit abono')
                                <a href="{{ route('abono.edit', $abono->id_abono) }}">Editar</a>
                            @endcan
                            @can('delete abono')
                                <form action="{{ route('abono.destroy', $abono->id_abono) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit">Eliminar</button>
                                </form>
                            @endcan
                                <form action="{{ route('abono.aplicar', $abono->id_abono) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit">Aplicar al crédito</button>
                                </form>

                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </section>
</body>
</html>
