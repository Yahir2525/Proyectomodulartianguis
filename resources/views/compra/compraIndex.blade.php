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
    <title>Principal de Compras</title>
</head>
<body>
<section>
  @if($compraIndex->isNotEmpty())
        <br><h2>Tablas de compras registradas</h2>
        <center>
            <table border="1">
                <tr>
                    <th>ID de la compra</th>
                    <th>Nombre del Cliente</th>
                    <th>Monto</th>
                    <th>Tipo</th>
                    <th>Cantidad</th>
                    <th>Acciones</th>
                </tr>
                @foreach ($compraIndex as $compra)
                    <tr>
                        <td>{{ $compra->id_compra }}</td>
                        <td>{{ optional($compra->user)->nombre_usuario ?? 'Sin cliente' }}</td>
                        <td>{{ number_format($compra->total_pagar, 2) }}</td>
                        <td>{{ $compra->created_at }}</td>
                        <td>{{ $compra->updated_at }}</td>
                        <td>
                            <form action="{{ route('compra.destroy', $compra->id_compra) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="button is-danger">Eliminar Compra</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </table>
        </center>
    @endif
</section>
</body>
</html>