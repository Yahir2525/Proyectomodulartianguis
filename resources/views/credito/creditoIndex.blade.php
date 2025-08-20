<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/credito/creditoIndex.css') }}">
    <title>Principal de créditos</title>
    <style>
        h2 { margin-top: 30px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: center; }
        .cerrado { background-color: #f2f2f2; }
    </style>
</head>
<body>
<section>
    <div>
        <h1>Principal de créditos</h1>
        <br>

        <a href="{{ url('/credito/create') }}" class="button is-info is-fullwidth">
            Registrar un nuevo crédito
        </a>
        <br><br>

        <form action="{{ url('/credito/showCredito') }}" method="GET">
            <label for="busqueda">Buscar por ID de crédito o nombre de usuario:</label>
            <input 
                type="text" 
                id="busqueda" 
                name="busqueda"
                placeholder="Ej. 21 o Pepito"
                @if(Auth::user()->can('edit credito')) list="usuarios" @endif
                value="{{ old('busqueda', request('busqueda')) }}" 
                autocomplete="off"
            />

            @can('edit credito')
                <datalist id="usuarios">
                    @foreach($usuarios as $usuario)
                        <option value="{{ $usuario->nombre_usuario }}"></option>
                    @endforeach
                </datalist>
            @endcan

            <input type="submit" value="Buscar" />
        </form>

        @if($creditoIndex->isNotEmpty())
            @php
                $agrupados = $creditoIndex->groupBy('user.nombre_usuario');
            @endphp

            @foreach($agrupados as $usuario => $creditosUsuario)
                <h2>Créditos de {{ $usuario ?? 'Usuario desconocido' }}</h2>

                @php
                    $ahora = now();
                    $activos = $creditosUsuario->filter(function($c) use ($ahora) {
                        return (int)$c->estado === 1 && \Carbon\Carbon::parse($c->fecha_vencimiento) >= $ahora;
                    });
                    $vencidos = $creditosUsuario->filter(function($c) use ($ahora) {
                        return (int)$c->estado === 1 && \Carbon\Carbon::parse($c->fecha_vencimiento) < $ahora;
                    });
                    $cerrados = $creditosUsuario->where('estado', 0);
                @endphp

                {{-- Créditos Activos --}}
                @if($activos->isNotEmpty())
                    <h3>Activos</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fecha de creación</th>
                                <th>Fecha Liquidación</th>
                                <th>Fecha Vencimiento</th>
                                <th>Saldo</th>
                                <th>Eliminar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activos as $credito)
                                <tr>
                                    <td>{{ $credito->id_credito }}</td>
                                    <td>{{ $credito->created_at }}</td>
                                    <td>{{ $credito->fecha_liquidacion ?? 'Aun no liquidado' }}</td>
                                    <td>{{ $credito->fecha_vencimiento }}</td>
                                    <td>${{ number_format($credito->saldo_total, 2) }}</td>
                                    <td>
                                        <form action="{{ url('/credito', $credito->id_credito) }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button type="submit">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

                {{-- Créditos Vencidos (estado 1 pero fecha_vencimiento pasada) --}}
                @if($vencidos->isNotEmpty())
                    <h3>Vencidos</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fecha de creación</th>
                                <th>Fecha Liquidación</th>
                                <th>Fecha Vencimiento</th>
                                <th>Saldo</th>
                                <th>Eliminar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vencidos as $credito)
                                <tr>
                                    <td>{{ $credito->id_credito }}</td>
                                    <td>{{ $credito->created_at }}</td>
                                    <td>{{ $credito->fecha_liquidacion ?? 'Aun no liquidado' }}</td>
                                    <td>{{ $credito->fecha_vencimiento }}</td>
                                    <td>${{ number_format($credito->saldo_total, 2) }}</td>
                                    <td>
                                        <form action="{{ url('/credito', $credito->id_credito) }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button type="submit">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

                {{-- Créditos Cerrados --}}
                @if($cerrados->isNotEmpty())
                    <h3>Cerrados</h3>
                    <table class="cerrado">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fecha Liquidación</th>
                                <th>Fecha Vencimiento</th>
                                <th>Saldo</th>
                                <th>Eliminar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cerrados as $credito)
                                <tr>
                                    <td>{{ $credito->id_credito }}</td>
                                    <td>{{ $credito->fecha_liquidacion ?? '-' }}</td>
                                    <td>{{ $credito->fecha_vencimiento }}</td>
                                    <td>${{ number_format($credito->saldo_total, 2) }}</td>
                                    <td>
                                        <form action="{{ url('/credito', $credito->id_credito) }}" method="POST">
                                            @csrf @method('DELETE')
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
            <p>No hay créditos registrados.</p>
        @endif

    </div>
</section>
</body>
</html>
