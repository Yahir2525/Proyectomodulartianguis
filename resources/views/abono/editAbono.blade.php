<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/abono/editAbono.css') }}">
    <link rel="icon" href="{{ asset('img/blanco.ico') }}" type="image/x-icon">
    <title>Editar abono</title>
</head>
<body>
    <div class="page-container">
        <main class="content">
        <br><x-barracreate/>
            <section class="container">
                <br><hr class="hr-grueso"><center><h1>Editar abono #{{ $abono->id_abono }}</h1></center><hr class="hr-grueso"><br>

                <p><strong>Usuario:</strong></p>
                <p class="datoabono">{{ $abono->user->nombre_usuario ?? 'Usuario no disponible' }}</p>

                <p><strong>Fecha de abono:</strong></p>
                <p class="datoabono">{{ $abono->created_at->format('d/m/Y H:i') }}</p>

                @if ($errors->any())
                    <div style="color: red;">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('abono.update', $abono->id_abono) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="monto_abono">Monto del abono:</label>
                        <input type="number" name="monto_abono" id="monto_abono" step="0.01" min="0" value="{{ old('monto_abono', $abono->monto_abono) }}" required>
                    </div>

                    <div>
                        <label for="id_credito">Seleccionar crédito:</label>
                        <select name="id_credito" id="id_credito" required>
                            <option value="">-- Selecciona un crédito --</option>
                            @foreach ($creditos as $credito)
                                <option value="{{ $credito->id_credito }}"
                                    {{ $abono->id_credito == $credito->id_credito ? 'selected' : '' }}
                                    {{ ($credito->estado == 0 || $credito->saldo_total <= 0) ? 'disabled' : '' }}>
                                    Crédito #{{ $credito->id_credito }} -
                                    Saldo: ${{ number_format($credito->saldo_total, 2) }}
                                    @if($credito->estado == 0) (cerrado) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Actualizar abono</button>

                </form><br>
                
                <center><div class="back-wrap">
                    <a href="{{ route('abono.index') }}" class="btn btn-danger">Cancelar</a>
                </div></center>
            </section>
        </main>
        <x-footer/>
    </div>
</body>
</html>
