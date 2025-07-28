<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Abono</title>
    <script>
        function filtrarCreditos() {
            const usuarioId = document.getElementById('id_user').value;
            const opciones = document.querySelectorAll('#id_credito option');

            opciones.forEach(op => {
                if (!op.dataset.user || usuarioId === '') {
                    op.style.display = 'none';
                } else {
                    op.style.display = (op.dataset.user === usuarioId) ? 'block' : 'none';
                }
            });

            document.getElementById('id_credito').value = '';
        }

        document.addEventListener("DOMContentLoaded", () => {
            const userSelect = document.getElementById('id_user');
            if (userSelect) userSelect.addEventListener('change', filtrarCreditos);

            filtrarCreditos(); // por si hay datos ya seleccionados
        });
    </script>
</head>
<body>
    <h1>Registrar nuevo abono</h1>

    @if ($errors->any())
        <div style="color: red;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('abono.store') }}" method="POST">
        @csrf

        {{-- Usuario --}}
        @if (Auth::user()->hasRole('administrador'))
            <label for="id_user">Seleccionar Usuario:</label>
            <select name="id_user" id="id_user" required>
                <option value="">-- Selecciona un usuario --</option>
                @foreach ($usuarios as $usuario)
                    <option value="{{ $usuario->id_user }}">{{ $usuario->nombre_usuario }}</option>
                @endforeach
            </select>
        @else
            <input type="hidden" name="id_user" value="{{ Auth::id() }}">
        @endif

        <br><br>

        {{-- Crédito (solo los activos) --}}
        <label for="id_credito">Seleccionar Crédito:</label>
        <select name="id_credito" id="id_credito" required>
            <option value="">-- Selecciona un crédito --</option>
            @foreach ($creditos->where('estado', 1) as $credito)
                <option 
                    value="{{ $credito->id_credito }}"
                    data-user="{{ $credito->id_user }}"
                >
                    Crédito #{{ $credito->id_credito }} - Usuario: {{ $credito->user->nombre_usuario ?? 'N/A' }} - Saldo: ${{ number_format($credito->saldo_total, 2) }}
                </option>
            @endforeach
        </select>

        <br><br>

        {{-- Monto --}}
        <label for="monto_abono">Monto del abono:</label>
        <input type="number" name="monto_abono" id="monto_abono" min="1" step="0.01" required>

        <br><br>

        <button type="submit">Aplicar al crédito</button>
    </form>
</body>
</html>
