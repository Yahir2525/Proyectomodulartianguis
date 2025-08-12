<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Abono</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 16px; font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; }
        h1 { max-width: 720px; margin: 0 auto 16px; }

        /* centrar y dar respiro al formulario */
        form { max-width: 720px; margin: 0 auto; display: grid; gap: 12px; }

        /* estilos suaves de campos */
        label { font-weight: 600; }
        input[type="number"], select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        button {
            padding: 10px 14px;
            border: 0;
            border-radius: 6px;
            background: #0d6efd;
            color: #fff;
            cursor: pointer;
        }
        button:hover { background: #0b5ed7; }

        /* mensajes de error */
        [style*="color: red;"] {
            max-width: 720px;
            margin: 0 auto 12px;
        }

        /* enlace volver */
        a[href*="abono.index"] { display: inline-block; margin: 12px auto 0; max-width: 720px; color: #0d6efd; }

        /* en móviles todo fluye a 100% */
        @media (max-width: 640px) {
            button, a[href*="abono.index"] { width: 100%; text-align: center; }
        }
    </style>
</head>
<body>
    <h1>Editar Abono #{{ $abono->id_abono }}</h1>

    {{-- Información adicional del abono --}}
    <p><strong>Usuario:</strong> {{ $abono->user->nombre_usuario ?? 'Usuario no disponible' }}</p>
    <p><strong>Fecha de abono:</strong> {{ $abono->created_at->format('d/m/Y H:i') }}</p>

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
        </div>

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
        <button type="submit">Actualizar Abono</button>
    </form>

    <br>
    <a href="{{ route('abono.index') }}">← Volver al listado de abonos</a>
</body>
</html>
