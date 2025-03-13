<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <link rel="icon" type="image/x-icon" href="/img/yourico.ico" />
    <link rel="stylesheet" href="{{ asset('css/aceite/indexAceite.css') }}">
    <link href="https://fonts.googleapis.com/css?family=Questrial&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/bulma@0.9.4/css/bulma.min.css" />
    <link rel="stylesheet" type="text/css" href="{{ asset('css/aceite/indexAceite.css') }}"> -->
    <title>Principal de abonos</title>
</head>
<body>
    <section>
        <div>
            <h1>Principal de abonos</h1>
            <br>
            <a href="{{ url('/abono/create') }}" class="button is-info is-fullwidth">
                Registrar una nueva compra
            </a><br><br>
            <form action="{{ url('/abono/showAbono') }}" method="GET"> 
                <div class="sub">
                    <label for="id">ID de compra a buscar:</label>
                    <input type="text" id="id" name="id_abono" placeholder="21" autofocus>
                </div><br><br>
                <input type="submit" id="enviar" name="enviar" value="buscar">
            </form>
            @if($abonoIndex->isNotEmpty())
                <br><h2>Tablas de abonos registrados</h2>
                @foreach ($abonoIndex as $abono)
                    <center>
                    <table>
                    <tr>
                        <th colspan="2">Tabla del abono: {{ $abono->id_abono }}</th>
                    </tr>
                    <tr>
                        <th>Atributo</th>
                        <th>Valor</th>
                    </tr>
                    <tr>
                        <td>ID</td>
                        <td>{{ $abono->id_abono }}</td>
                    </tr>
                    <tr>
                        <td>ID del credito</td>
                        <td>{{ optional($abono->credito)->id_credito ?? 'Sin credito' }}</td>
                            </tr>
                    <tr>
                        <td>Nombre de usuario</td>
                        <td>{{ optional($abono->cliente) ? $abono->nombre_usuario : 'No tiene usuario' }}</td>
                        </tr>
                    <tr>
                        <td>Monto</td>
                        <td>{{ $abono->monto_abono }}</td>
                    </tr>
                    <tr>
                        <td>Creador</td>
                        <td>{{ $abono->created_at }}</td>
                    </tr>
                    <tr>
                        <td>Actualizado</td>
                        <td>{{ $abono->updated_at }}</td>
                    </tr>
                </table>
                        <br>
                        <a href="{{ route('abono.edit', $abono->id_abono) }}" class="button is-primary">Editar Compra</a>
                        <form action="{{ route('abono.destroy', $abono->id_abono) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <br><button type="submit" class="button is-danger">Eliminar Abono</button>
                        </form>
                    </center>
                @endforeach 
            @endif
        </div>
    </section>
</body>
</html>
