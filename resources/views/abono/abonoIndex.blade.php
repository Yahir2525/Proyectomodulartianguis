<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/abono/abonoIndex.css') }}">
    <title>Principal de abonos</title>
</head>
<body>
<div class="page-container">
<main class="content">
<br><x-barrageneral/>
<section class="container">
<br><hr class="hr-grueso"><center><h1>Historial de Abonos</h1></center><hr class="hr-grueso"><br>

        @can('create abono')
        <form action="{{ url('/abono/create') }}" method="GET" style="display:inline;">
            <button type="submit" class="btn btn-primary">Registrar nuevo abono</button>
        </form><br><br>
        @endcan
        <form action="{{ url('/abono/showAbono') }}" method="GET">
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
        </form>

        @if ($abonoIndex->isNotEmpty())
            @php
                // Agrupar por crédito (si no tiene, va al grupo "Sin crédito")
                $abonosAgrupados = $abonoIndex->groupBy(function($abono) {
                    return optional($abono->credito)->id_credito ?? 'Sin crédito';
                });
            @endphp

            @foreach ($abonosAgrupados as $idCredito => $abonos)
                @php
                    $usuarioNombre = optional($abonos->first()->user)->nombre_usuario;
                @endphp

                @if($idCredito === 'Sin crédito')
                    <h2>Abonos sin crédito @if($usuarioNombre) de {{ $usuarioNombre }} @endif</h2>
                @else
                    <h2>Abonos del crédito #{{ $idCredito }} @if($usuarioNombre) de {{ $usuarioNombre }} @endif</h2>
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
                            @foreach ($abonos as $abono)
                                @php
                                    $credito = $abono->credito;
                                    $estaCerrado = $credito && $credito->estado == 0;
                                    $estaVencido = $credito && $credito->fecha_vencimiento && $credito->fecha_vencimiento < now();
                                @endphp

                                <tr>
                                    <td data-label="ID">{{ $abono->id_abono }}</td>
                                    <td data-label="Creado">{{ $abono->created_at }}</td>
                                    <td data-label="Actualizado">{{ $abono->updated_at }}</td>
                                    <td data-label="Monto">${{ number_format($abono->monto_abono, 2) }}</td>
                                    
                                    <td data-label="Editar">
                                        @can('edit abono')
                                            @if($estaCerrado)
                                                <span class="info-msg text-danger">No editable (crédito cerrado)</span>
                                            @else
                                                <form action="{{ route('abono.edit', $abono->id_abono) }}" method="GET" style="display:inline;">
                                                    <button type="submit" class="btn btn-edit">Editar</button>
                                                </form>
                                            @endif
                                        @endcan
                                    </td>
                                    
                                    <td data-label="Eliminar">
                                        @can('delete abono')
                                            @if($estaCerrado)
                                                <span class="info-msg text-danger">No eliminable (crédito cerrado)</span>
                                            @elseif($estaVencido)
                                                <span class="info-msg text-warning">No eliminable (crédito vencido)</span>
                                            @else
                                                <form action="{{ route('abono.destroy', $abono->id_abono) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-danger" type="submit">Eliminar</button>
                                                </form>
                                            @endif
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        @else
            <p>No hay abonos registrados.</p>
        @endif
</section>
</main>
<x-footer/>
</div>
</body>
</html>
