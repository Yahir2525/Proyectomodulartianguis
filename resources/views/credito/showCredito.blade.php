<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <title>Detalle(s) de Crédito</title>
    <style>
        table { border-collapse: collapse; width: 100%; margin-bottom: 30px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: center; }
        th { background-color: #eee; }
        h2 { margin-top: 40px; color: #2c3e50; }
        h3 { margin-top: 20px; color: #34495e; }
        p.error { color: red; font-weight: bold; }
        a { display: inline-block; margin-top: 15px; color: #2980b9; text-decoration: none; }
        a:hover { text-decoration: underline; }
        button { background-color: #c0392b; color: white; border: none; padding: 6px 10px; cursor: pointer; border-radius: 3px; }
        button:hover { background-color: #e74c3c; }
    </style>
</head>
<body>
    <h1>Detalle(s) de Crédito</h1>

    @if(session('error'))
        <p class="error">{{ session('error') }}</p>
    @endif

    @if(isset($creditos) && $creditos->isNotEmpty())
        @php
            $creditosPorUsuario = $creditos->groupBy(fn($c) => optional($c->user)->nombre_usuario ?? 'Usuario desconocido');
        @endphp

        @foreach($creditosPorUsuario as $usuario => $grupoCreditos)
            <h2>Créditos de {{ $usuario }}</h2>

            @php
                $ahora = now();
                $activos = $grupoCreditos->filter(function($c) use ($ahora) {
                    return (int)$c->estado === 1 && \Carbon\Carbon::parse($c->fecha_vencimiento) >= $ahora;
                });
                $vencidos = $grupoCreditos->filter(function($c) use ($ahora) {
                    return (int)$c->estado === 1 && \Carbon\Carbon::parse($c->fecha_vencimiento) < $ahora;
                });
                $cerrados = $grupoCreditos->where('estado', 0);
            @endphp

            @if($activos->isNotEmpty())
                <h3>Activos</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID Crédito</th>
                            <th>Fecha de creación</th>
                            <th>Fecha de liquidación</th>
                            <th>Fecha de vencimiento</th>
                            <th>Saldo total</th>
                            <th>Eliminar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activos as $credito)
                            <tr>
                                <td>{{ $credito->id_credito }}</td>
                                <td>{{ $credito->created_at }}</td>
                                <td>{{ $credito->fecha_liquidacion ?? 'Aún no liquidado' }}</td>
                                <td>{{ $credito->fecha_vencimiento }}</td>
                                <td>${{ number_format($credito->saldo_total, 2) }}</td>
                                <td>
                                    <form action="{{ url('/credito', $credito->id_credito) }}" method="POST" onsubmit="return confirm('¿Eliminar este crédito?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            @if($vencidos->isNotEmpty())
                <h3>Vencidos</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID Crédito</th>
                            <th>Fecha de creación</th>
                            <th>Fecha de liquidación</th>
                            <th>Fecha de vencimiento</th>
                            <th>Saldo total</th>
                            <th>Eliminar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vencidos as $credito)
                            <tr>
                                <td>{{ $credito->id_credito }}</td>
                                <td>{{ $credito->created_at }}</td>
                                <td>{{ $credito->fecha_liquidacion ?? 'Aún no liquidado' }}</td>
                                <td>{{ $credito->fecha_vencimiento }}</td>
                                <td>${{ number_format($credito->saldo_total, 2) }}</td>
                                <td>
                                    <form action="{{ url('/credito', $credito->id_credito) }}" method="POST" onsubmit="return confirm('¿Eliminar este crédito?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            @if($cerrados->isNotEmpty())
                <h3>Cerrados</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID Crédito</th>
                            <th>Fecha de creación</th>
                            <th>Fecha de liquidación</th>
                            <th>Fecha de vencimiento</th>
                            <th>Saldo total</th>
                            <th>Eliminar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cerrados as $credito)
                            <tr>
                                <td>{{ $credito->id_credito }}</td>
                                <td>{{ $credito->created_at }}</td>
                                <td>{{ $credito->fecha_liquidacion ?? '-' }}</td>
                                <td>{{ $credito->fecha_vencimiento }}</td>
                                <td>${{ number_format($credito->saldo_total, 2) }}</td>
                                <td>
                                    <form action="{{ url('/credito', $credito->id_credito) }}" method="POST" onsubmit="return confirm('¿Eliminar este crédito?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

        @endforeach
    @else
        <p>No se encontraron créditos.</p>
    @endif

    <a href="{{ url('/credito') }}">← Volver al listado de créditos</a>
</body>
</html>
