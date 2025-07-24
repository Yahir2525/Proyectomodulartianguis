<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle(s) de Crédito</title>
    <style>
        table { border-collapse: collapse; width: 100%; margin-bottom: 30px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: center; }
        th { background-color: #eee; }
    </style>
</head>
<body>
    <h1>Detalle(s) de Crédito</h1>

    @if(session('error'))
        <p style="color:red">{{ session('error') }}</p>
    @endif

    @if(isset($creditos))
        <table>
            <thead>
                <tr>
                    <th>ID Crédito</th>
                    <th>Usuario</th>
                    <th>Fecha Liquidación</th>
                    <th>Fecha Vencimiento</th>
                    <th>Estado</th>
                    <th>Saldo Total</th>
                    <th>Creado</th>
                    <th>Actualizado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($creditos as $credito)
                    <tr>
                        <td>{{ $credito->id_credito }}</td>
                        <td>{{ optional($credito->user)->nombre_usuario ?? 'Sin usuario' }}</td>
                        <td>{{ $credito->fecha_liquidacion }}</td>
                        <td>{{ $credito->fecha_vencimiento }}</td>
                        <td>{{ $credito->estado ? 'Activo' : 'Inactivo' }}</td>
                        <td>${{ number_format($credito->saldo_total, 2) }}</td>
                        <td>{{ $credito->created_at }}</td>
                        <td>{{ $credito->updated_at }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <br>
    <a href="{{ url('/credito') }}">← Volver al listado de créditos</a>
</body>
</html>
