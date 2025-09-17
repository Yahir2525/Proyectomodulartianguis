<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/credito/creditoIndex.css') }}">
    <link rel="icon" href="{{ asset('img/blanco.ico') }}" type="image/x-icon">
    <title>Detalles del crédito</title>
</head>
<body>
    <br><x-barrageneral/>
    <div class="page-container">
        <main class="content">
            <section class="container">
                <br><hr class="hr-grueso"><h1>Detalles del crédito</h1><hr class="hr-grueso"><br>

                @if(session('error'))
                    <p class="error">{{ session('error') }}</p>
                @endif

                @can('view credito')
                <form action="{{ url('/credito/showCredito') }}" method="GET">
                    <label for="buscar">Buscar crédito:</label>
                    <input 
                        type="text"
                        id="buscar"
                        name="buscar"
                        placeholder="Ej. 21"
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

                </form>
                @endcan

                @if(isset($creditos) && $creditos->isNotEmpty())
                    @php
                        $creditosPorUsuario = $creditos->groupBy(fn($c) => optional($c->user)->nombre_usuario ?? 'Usuario desconocido');
                    @endphp

                    @foreach($creditosPorUsuario as $usuario => $grupoCreditos)
                        <h2>Créditos de {{ $usuario }}</h2>

                        @php
                            $ahora = now();
                            $activos = $grupoCreditos->filter(fn($c) => (int)$c->estado === 1 && \Carbon\Carbon::parse($c->fecha_vencimiento) >= $ahora);
                            $vencidos = $grupoCreditos->filter(fn($c) => (int)$c->estado === 1 && \Carbon\Carbon::parse($c->fecha_vencimiento) < $ahora);
                            $cerrados = $grupoCreditos->where('estado', 0);
                        @endphp

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
                                        @if($activos)
                                        <th>Estado</th>
                                        @endif
                                        <!-- <th>Eliminar</th> -->
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
                                            @if($activos)
                                            <td data-label="Estado"><span class="badge bg-activo">Crédito activo</span></td>
                                            @endif
                                            <!-- <td data-label="Eliminar">
                                                <form action="{{ url('/credito', $credito->id_credito) }}" method="POST" style="display:inline" onsubmit="return confirm('¿Eliminar este crédito?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">Eliminar</button>
                                                </form>
                                            </td> -->
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            </div>
                        @endif

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
                                        @if($vencidos)
                                        <th>Estado</th>
                                        @endif
                                        <!-- <th>Eliminar</th> -->
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
                                            @if($vencidos)
                                            <td data-label="Estado"><span class="badge bg-vencido">Crédito vencido</span></td>
                                            @endif
                                            <!-- <td data-label="Eliminar">
                                                <form action="{{ url('/credito', $credito->id_credito) }}" method="POST" style="display:inline" onsubmit="return confirm('¿Eliminar este crédito?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">Eliminar</button>
                                                </form>
                                            </td> -->
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            </div>
                        @endif

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
                                        @if($cerrados)
                                        <th>Estado</th>
                                        @endif
                                        <!-- <th>Eliminar</th> -->
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
                                            @if($cerrados)
                                            <td data-label="Estado"><span class="badge bg-cerrado">Crédito cerrado</span></td>
                                            @endif
                                            <!-- <td data-label="Eliminar">
                                                <form action="{{ url('/credito', $credito->id_credito) }}" method="POST" style="display:inline" onsubmit="return confirm('¿Eliminar este crédito?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">Eliminar</button>
                                                </form>
                                            </td> -->
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            </div>
                        @endif
                    @endforeach
                @else
                    <p>No se encontraron créditos.</p>
                @endif <br><br>
                
                <center><a href="{{ url('/carro') }}" class="btn btn-secondary fw-bold">Volver al listado</a></center>
            </section>
        </main>
        <x-footer/>
    </div>
</body>
</html>
