<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle(s) de Abono</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        th { background-color: #f2f2f2; }
        h2 { margin-top: 30px; }
        form { display: inline; }
        .btn { padding: 5px 10px; margin: 0 2px; text-decoration: none; border: 1px solid #ccc; background: #eee; border-radius: 4px; }
        .btn:hover { background-color: #ddd; }

        /* --- Responsivo --- */
        * { box-sizing: border-box; }
        @media (max-width: 768px) {
            /* la tabla se vuelve desplazable horizontalmente */
            table { display: block; overflow-x: auto; -webkit-overflow-scrolling: touch; }
            /* evita que el contenido “rompa” al hacerse muy estrecho */
            thead, tbody, th, td, tr { white-space: nowrap; }

            /* acciones más cómodas en móvil: apilar botones */
            .btn { display: block; width: 100%; margin: 6px 0; }
            form[method="POST"] { display: block; } /* para que el botón de eliminar no quede en la misma línea */
        }
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
        @php
            $abonosPorUsuario = $listaAbonos->groupBy(fn($a) => optional($a->user)->nombre_usuario ?? 'Usuario desconocido');
        @endphp

        @foreach($abonosPorUsuario as $nombreUsuario => $grupoAbonos)
            <h2>Abonos de {{ $nombreUsuario }}</h2>
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
                    @foreach($grupoAbonos as $abono)
                        <tr>
                            <td>{{ $abono->id_abono }}</td>
                            <td>{{ optional($abono->user)->nombre_usuario ?? 'Usuario no disponible' }}</td>
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
        @endforeach
    @endif

    <a href="{{ url('/abono') }}">← Volver al listado</a>
</body>
</html>
