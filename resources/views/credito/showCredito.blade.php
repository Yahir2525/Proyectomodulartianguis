<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle(s) de Crédito</title>
    <style>
        table { border-collapse: collapse; width: 100%; margin-bottom: 30px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: center; }
        th { background-color: #eee; }
        h2 { margin-top: 40px; }
    </style>
</head>
<body>
    <h1>Detalle(s) de Crédito</h1>

    @if(session('error'))
        <p style="color:red">{{ session('error') }}</p>
    @endif

    @if(isset($creditos) && $creditos->isNotEmpty())
        @php
            $creditosPorUsuario = $creditos->groupBy(fn($c) => optional($c->user)->nombre_usuario ?? 'Usuario desconocido');
        @endphp

        @foreach($creditosPorUsuario as $usuario => $grupoCreditos)
            <h2>Créditos de {{ $usuario }}</h2>

            @php
                $porEstado = $grupoCreditos->groupBy(fn($c) => $c->estado ? 'Activos' : 'Cerrados');
            @endphp

            @foreach($porEstado as $estado => $creditosPorEstado)
                <h3>{{ $estado }}</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID Crédito</th>
                            <th>Fecha de creación</th>
                            <th>Fecha Liquidación</th>
                            <th>Fecha Vencimiento</th>
                            <th>Saldo Total</th>
                            <th>Eliminar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($creditosPorEstado as $credito)
                            <tr>
                                <td>{{ $credito->id_credito }}</td>
                                <td>{{ $credito->created_at }}</td>
                                <td>{{ $credito->fecha_liquidacion ?? 'Aun no liquidado'}}</td>
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
            @endforeach
        @endforeach
    @else
        <p>No se encontraron créditos.</p>
    @endif

    <br>
    <a href="{{ url('/credito') }}">← Volver al listado de créditos</a>
</body>
</html>
