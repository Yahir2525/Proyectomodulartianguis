<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle(s) de Abono</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/abono/showAbono.css') }}">
</head>
<body>
<section class="container">
     <br><hr class="hr-grueso"><center><h1>Detalles del Abono</h1></center><hr class="hr-grueso">

    @php
        $listaAbonos = isset($abonos) ? $abonos : (isset($abono) ? collect([$abono]) : collect([]));
    @endphp

    @if($listaAbonos->isEmpty())
        <p>No se encontraron abonos para mostrar.</p>
    @else
        @php
            // Agrupar por crédito; si no tiene, va al grupo "Sin crédito"
            $abonosPorCredito = $listaAbonos->groupBy(function($a) {
                return optional($a->credito)->id_credito ?? 'Sin crédito';
            });
        @endphp

        @foreach($abonosPorCredito as $idCredito => $grupoAbonos)
            @php
                $nombreUsuario = optional($grupoAbonos->first()->user)->nombre_usuario ?? 'Usuario desconocido';
            @endphp

            @if($idCredito === 'Sin crédito')
                <h2>Abonos sin crédito @if($nombreUsuario) de {{ $nombreUsuario }} @endif</h2>
            @else
                <h2>Abonos del crédito #{{ $idCredito }} @if($nombreUsuario) de {{ $nombreUsuario }} @endif</h2>
            @endif

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>ID Abono</th>
                            <th>ID Usuario</th>
                            <th>ID Crédito</th>
                            <th>Monto</th>
                            <th>Fecha</th>
                            <th>Editar</th>
                            <th>Eliminar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($grupoAbonos as $abono)
                            <tr>
                                <td>{{ $abono->id_abono }}</td>
                                <td>{{ optional($abono->user)->nombre_usuario ?? 'Usuario no disponible' }}</td>
                                <td>{{ optional($abono->credito)->id_credito ?? 'Sin crédito' }}</td>
                                <td>${{ number_format($abono->monto_abono, 2) }}</td>
                                <td>{{ $abono->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    @can('edit abono')
                                        <a href="{{ route('abono.edit', $abono->id_abono) }}" class="btn btn-edit">Editar</a>
                                    @endcan
                                </td>
                                <td>
                                    @can('delete abono')
                                        <form action="{{ route('abono.destroy', $abono->id_abono) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">Eliminar</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    @endif

    <br><div class="back-wrap">
        <a href="{{ route('abono.index') }}" class="btn btn-warning">Volver al historial de abonos</a>
    </div>
</section>
</body>
</html>
