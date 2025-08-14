<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Abono</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/abono/editAbono.css') }}">
</head>
<body>
    <section class="container">
    <br><hr class="hr-grueso"><center><h1>Editar Abono #{{ $abono->id_abono }}</h1></center><hr class="hr-grueso"><br>

    {{-- Información adicional del abono --}}
    <p><strong>Usuario:</strong> {{ $abono->user->nombre_usuario ?? 'Usuario no disponible' }}</p><br>
    <p><strong>Fecha de abono:</strong> {{ $abono->created_at->format('d/m/Y H:i') }}</p><br>

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
            <label for="monto_abono">Monto del Abono:</label>
            <input type="number" name="monto_abono" id="monto_abono" step="0.01" min="0" value="{{ old('monto_abono', $abono->monto_abono) }}" required>
        </div><br>

        <div>
            <label for="id_credito">Seleccionar Crédito:</label>
            <select name="id_credito" id="id_credito" required>
                <option value="">-- Selecciona un crédito --</option>
                @foreach ($creditos as $credito)
                    <option value="{{ $credito->id_credito }}"
                        {{ $abono->id_credito == $credito->id_credito ? 'selected' : '' }}>
                        Crédito #{{ $credito->id_credito }} -
                        Saldo: ${{ number_format($credito->saldo_total, 2) }}
                        @if($credito->estado == 0) (cerrado) @endif
                    </option>
                @endforeach
            </select>
        </div>

        <br>
        <button type="submit" class="btn btn-primary">Actualizar Abono</button>
    </form>

    <br><center>
    <div class="back-wrap">
    <a href="{{ route('abono.index') }}" class="btn btn-primary">Cancelar</a>
    </div></center>
    </section>
</body>
</html>
