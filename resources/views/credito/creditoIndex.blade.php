<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/credito/creditoIndex.css') }}">
    <title>Principal de créditos</title>
</head>
<body>
<div class="page-container">
<main class="content">
<br><x-barrageneral/>
<section class="container">
    <br><hr class="hr-grueso"><center><h1>Listado de créditos</h1></center><hr class="hr-grueso"><br>
    <!-- Formulario de búsqueda -->
    <form action="{{ url('/credito/showCredito') }}" method="GET" class="buscar">
        <label for="buscar">Buscar crédito:</label>
        <input 
            type="text"
            id="buscar"
            name="buscar"
            placeholder="Ej. 21 o Pepito"
            list="{{ Auth::user()->hasRole('administrador') ? 'usuarios' : '' }}"
            value="{{ request('buscar') }}"
            autocomplete="off"
        >

        @if(Auth::user()->hasRole('administrador'))
            <datalist id="usuarios">
                @foreach($usuarios as $usuario)
                    <option value="{{ $usuario->nombre_usuario }}"></option>
                @endforeach
            </datalist>
        @endif

        <input type="submit" value="Buscar">
    </form><br>


    @if($creditoIndex->isNotEmpty())
        @php
            $agrupados = $creditoIndex->groupBy('user.nombre_usuario');
        @endphp

        @foreach($agrupados as $usuario => $creditosUsuario)
            <h2>Créditos de {{ $usuario ?? 'Usuario desconocido' }}</h2>

            @php
                $ahora = now();
                $activos = $creditosUsuario->filter(fn($c) => $c->estado == 1 && $c->fecha_vencimiento >= $ahora);
                $vencidos = $creditosUsuario->filter(fn($c) => $c->estado == 1 && $c->fecha_vencimiento < $ahora);
                $cerrados = $creditosUsuario->filter(fn($c) => $c->estado == 0);
            @endphp

            {{-- Activos --}}
            @if($activos->isNotEmpty())
                <h3>Activos</h3>
                <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Creación</th>
                            <th>Liquidación</th>
                            <th>Vencimiento</th>
                            <th>Saldo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activos as $credito)
                            <tr>
                                <td data-label="ID">{{ $credito->id_credito }}</td>
                                <td data-label="Creado">{{ $credito->created_at }}</td>
                                <td data-label="Liquidado">{{ $credito->fecha_liquidacion ?? 'Aún no liquidado' }}</td>
                                <td data-label="Vencimiento">{{ $credito->fecha_vencimiento }}</td>
                                <td data-label="Saldo">${{ number_format($credito->saldo_total, 2) }}</td>
                                <td data-label="Eliminar">
                                    <form action="{{ url('/credito', $credito->id_credito) }}" method="POST" style="display:inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            @endif

            {{-- Vencidos --}}
            @if($vencidos->isNotEmpty())
                <h3>Vencidos</h3>
                <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Creación</th>
                            <th>Liquidación</th>
                            <th>Vencimiento</th>
                            <th>Saldo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vencidos as $credito)
                            <tr>
                                <td data-label="ID">{{ $credito->id_credito }}</td>
                                <td data-label="Creado">{{ $credito->created_at }}</td>
                                <td data-label="Liquidado">{{ $credito->fecha_liquidacion ?? 'Aún no liquidado' }}</td>
                                <td data-label="Vencimiento">{{ $credito->fecha_vencimiento }}</td>
                                <td data-label="Saldo">${{ number_format($credito->saldo_total, 2) }}</td>
                                <td data-label="Eliminar">
                                    <form action="{{ url('/credito', $credito->id_credito) }}" method="POST" style="display:inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            @endif
            {{-- Cerrados --}}
            @if($cerrados->isNotEmpty())
                <h3>Cerrados</h3>
                <div class="table-wrap">
                <table class="cerrado">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Creación</th>
                            <th>Liquidación</th>
                            <th>Vencimiento</th>
                            <th>Saldo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cerrados as $credito)
                            <tr>
                                <td data-label="ID">{{ $credito->id_credito }}</td>
                                <td data-label="Creado">{{ $credito->created_at }}</td>
                                <td data-label="Liquidado">{{ $credito->fecha_liquidacion ?? '-' }}</td>
                                <td data-label="Vencimiento">{{ $credito->fecha_vencimiento }}</td>
                                <td data-label="Saldo">${{ number_format($credito->saldo_total, 2) }}</td>
                                <td data-label="Eliminar">
                                    <form action="{{ url('/credito', $credito->id_credito) }}" method="POST" style="display:inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            @endif

        @endforeach
    @else
        <p>No hay créditos registrados.</p>
    @endif
</section>
</main>
<x-footer/>
</div>
</body>
</html>
