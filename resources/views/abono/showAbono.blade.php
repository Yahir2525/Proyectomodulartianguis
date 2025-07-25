<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle(s) de Abono</title>
    <style>
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        th { background-color: #f2f2f2; }
        h2 { margin-top: 30px; }
        form { display: inline; }
        .btn { padding: 5px 10px; margin: 0 2px; text-decoration: none; border: 1px solid #ccc; background: #eee; border-radius: 4px; }
        .btn:hover { background-color: #ddd; }
    </style>
</head>
<body>
    <h1>Detalle(s) de Abono</h1>

    @php
        $listaAbonos = isset($abonos) ? $abonos : (isset($abono) ? collect([$abono]) : collect([]));
    @endphp

    @if($listaAbonos->isEmpty())
        <p>No se encontraron abonos para mostrar.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>ID Abono</th>
                    <th>ID Usuario</th>
                    <th>ID Crédito</th>
                    <th>Monto</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($listaAbonos as $abono)
                    <tr>
                        <td>{{ $abono->id_abono }}</td>
                        <td>{{ $abono->id_user }}</td>
                        <td>{{ $abono->id_credito }}</td>
                        <td>${{ number_format($abono->monto_abono, 2) }}</td>
                        <td>{{ $abono->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <a class="btn" href="{{ route('abono.edit', $abono->id_abono) }}">Editar</a>

                            <form action="{{ route('abono.destroy', $abono->id_abono) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este abono?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <a href="{{ url('/abono') }}">Volver al listado</a>
</body>
</html>
