<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Principal de abonos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Layout básico y tablas responsivas */
        .container { max-width: 1100px; margin: 0 auto; padding: 0 16px; }
        .table-wrap { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }

        table { border-collapse: collapse; width: 100%; margin-top: 10px; min-width: 720px; }
        th, td { border: 1px solid #999; padding: 5px; text-align: center; }
        h2 { margin-top: 30px; }

        /* Inputs/botones cómodos en móvil */
        form { display: grid; grid-template-columns: 1fr auto; gap: 8px; align-items: center; }
        @media (max-width: 640px) {
            form { grid-template-columns: 1fr; }
            input[type="text"], input[type="submit"], a { width: 100%; }
        }
    </style>
</head>
<body>
    <section class="container">
        <h1>Principal de abonos</h1><br>

        @can('create abono')
            <a href="{{ url('/abono/create') }}">Registrar nuevo abono</a>
        @endcan

        <br><br>

        <form action="{{ url('/abono/showAbono') }}" method="GET">
            <label for="busqueda">Buscar por ID de abono o nombre de usuario:</label>
            <input
                type="text"
                id="busqueda"
                name="busqueda"
                placeholder="Ej. 21 o Carlitos"
                list="{{ Auth::user()->can('edit abono') ? 'usuarios' : '' }}"
                value="{{ request('busqueda') }}"
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
                $abonosAgrupados = $abonoIndex->groupBy('user.nombre_usuario');
            @endphp

            @foreach ($abonosAgrupados as $usuario => $abonos)
                <h2>Abonos de {{ $usuario ?? 'Usuario desconocido' }}</h2>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>ID del abono</th>
                                <th>ID del crédito</th>
                                <th>Monto</th>
                                <th>Fecha de creación</th>
                                <th>Última actualización</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($abonos as $abono)
                                <tr>
                                    <td>{{ $abono->id_abono }}</td>
                                    <td>{{ optional($abono->credito)->id_credito ?? 'Sin crédito' }}</td>
                                    <td>${{ number_format($abono->monto_abono, 2) }}</td>
                                    <td>{{ $abono->created_at }}</td>
                                    <td>{{ $abono->updated_at }}</td>
                                    <td>
                                        @can('edit abono')
                                            <a href="{{ route('abono.edit', $abono->id_abono) }}">Editar</a>
                                        @endcan

                                        @can('delete abono')
                                            <form action="{{ route('abono.destroy', $abono->id_abono) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit">Eliminar</button>
                                            </form>
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
</body>
</html>
