<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/abono/showAbono.css') }}">
    <title>Detalle(s) de Abono</title>
</head>
<body>
<div class="page-container">
<main class="content">
<br><x-barrageneral/>
<section class="container">
    <br><hr class="hr-grueso"><center><h1>Detalles del Abono</h1></center><hr class="hr-grueso">
    <br>
    <form action="{{ url('/abono/showAbono') }}" method="GET" class="buscar">
        <label for="buscar">Buscar abono:</label>
        <input
            type="text"
            id="buscar"
            name="buscar"
            placeholder="Ej. 21 o Carlitos"
            list="{{ Auth::user()->can('edit abono') ? 'usuarios' : '' }}"
            value="{{ request('buscar') }}"
            autocomplete="off"
        />
        @can('edit abono')
            <datalist id="usuarios">
                @foreach($usuarios as $usuario)
                    <option value="{{ $usuario->nombre_usuario }}"></option>
                @endforeach
            </datalist>
        @endcan
        <input type="submit" value="Buscar" />
    </form><br>

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

            
            <div class="table-responsive table-wrap">
                    <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID del abono</th>
                            <th>Fecha de creación</th>
                            <th>Última actualización</th>
                            <th>Monto</th>
                            <th>Editar</th>
                            <th>Eliminar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($grupoAbonos as $abono)
                            <tr>
                                <td data-label="ID">{{ $abono->id_abono }}</td>
                                
                                <td data-label="Creado">{{ $abono->created_at }}</td>
                                <td data-label="Actualizado">{{ $abono->updated_at }}</td>
                                <td data-label="Monto">${{ number_format($abono->monto_abono, 2) }}</td>
                                <td data-label="Editar">
                                    @can('edit abono')
                                        <a href="{{ route('abono.edit', $abono->id_abono) }}" class="btn btn-edit">Editar</a>
                                    @endcan
                                </td>
                                <td data-label="Eliminar">
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
</main>
<x-footer/>
</div>
</body>
</html>
