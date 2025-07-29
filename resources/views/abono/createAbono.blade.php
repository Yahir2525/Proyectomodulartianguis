<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Abono</title>

    <script>
        function filtrarCreditos() {
            const usuarioInput = document.getElementById('nombre_usuario');
            const userIdInput = document.getElementById('id_user');
            const selectedName = usuarioInput.value.trim();
            const options = usuarioInput.list.options;
            let userId = '';

            // Buscar el ID del usuario seleccionado por nombre
            for (let i = 0; i < options.length; i++) {
                if (options[i].value === selectedName) {
                    userId = options[i].dataset.userid;
                    break;
                }
            }

            userIdInput.value = userId;

            const creditos = document.querySelectorAll('#id_credito option');

            creditos.forEach(op => {
                if (!op.dataset.user || userId === '') {
                    op.style.display = 'none';
                } else {
                    op.style.display = (op.dataset.user === userId) ? 'block' : 'none';
                }
            });

            document.getElementById('id_credito').value = '';
        }

        document.addEventListener("DOMContentLoaded", () => {
            const usuarioInput = document.getElementById('nombre_usuario');
            if (usuarioInput) {
                usuarioInput.addEventListener('input', filtrarCreditos);
                filtrarCreditos(); // inicial
            }
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
            <label for="nombre_usuario">Buscar usuario:</label>
            <input list="usuarios" id="nombre_usuario" placeholder="Ej. Juan Pérez" required>
            <input type="hidden" name="id_user" id="id_user">

            <datalist id="usuarios">
                @foreach ($usuarios as $usuario)
                    <option 
                        value="{{ $usuario->nombre_usuario }}"
                        data-userid="{{ $usuario->id_user }}"
                    >
                    @endforeach
            </datalist>
        @else
            <input type="hidden" name="id_user" value="{{ Auth::id() }}">
        @endif

        <br><br>

        {{-- Crédito activo y con saldo > 0 --}}
        <label for="id_credito">Seleccionar Crédito:</label>
        <select name="id_credito" id="id_credito" required>
            <option value="">-- Selecciona un crédito --</option>
            @foreach ($creditos->where('estado', 1)->filter(fn($c) => $c->saldo_total > 0) as $credito)
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
