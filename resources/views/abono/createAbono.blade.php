<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Abono</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        /* contenedor cómodo y centrado */
        .container { max-width: 720px; margin: 0 auto; padding: 16px; }

        /* formulario fluido en móvil */
        form { display: grid; gap: 12px; }
        label { font-weight: 600; }
        input[type="text"], input[type="number"], select {
            width: 100%; padding: 10px; box-sizing: border-box;
        }
        button { width: 100%; padding: 10px 14px; }

        /* agrupar campos en 2 columnas solo en pantallas medianas+ */
        .grid-2 { display: grid; gap: 12px; }
        @media (min-width: 768px) {
            .grid-2 { grid-template-columns: 1fr 1fr; }
        }

        /* mensajes de error */
        .errors { color: #b00020; }
    </style>

    <script>
        function filtrarCreditos() {
            const usuarioInput = document.getElementById('nombre_usuario');
            const userIdInput  = document.getElementById('id_user');
            const selectedName = usuarioInput.value.trim();
            const options      = usuarioInput.list ? usuarioInput.list.options : [];
            let userId = '';

            // Buscar el ID del usuario por nombre exacto del datalist
            for (let i = 0; i < options.length; i++) {
                if (options[i].value === selectedName) {
                    userId = options[i].dataset.userid || '';
                    break;
                }
            }
            if (userIdInput) userIdInput.value = userId;

            // mostrar/ocultar créditos del usuario (usar attribute hidden para <option>)
            const creditos = document.querySelectorAll('#id_credito option');
            creditos.forEach(op => {
                if (!op.value) { op.hidden = false; return; }     // placeholder
                const opUser = op.dataset.user || '';
                // si no hay usuario elegido, mostrar todos
                op.hidden = (userId !== '' && opUser !== userId);
            });

            // reset de selección
            const select = document.getElementById('id_credito');
            if (select) select.value = '';
        }

        document.addEventListener("DOMContentLoaded", () => {
            const usuarioInput = document.getElementById('nombre_usuario');
            if (usuarioInput) {
                // 'change' funciona mejor con datalist
                usuarioInput.addEventListener('change', filtrarCreditos);
                filtrarCreditos(); // inicial sin ocultar todo
            }
        });
    </script>
</head>
<body>
<div class="container">
    <h1>Registrar nuevo abono</h1>

    @if ($errors->any())
        <div class="errors" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('abono.store') }}" method="POST">
        @csrf

        <div class="grid-2">
            {{-- Usuario --}}
            <div>
                @if (Auth::user()->hasRole('administrador'))
                    <label for="nombre_usuario">Buscar usuario</label>
                    <input list="usuarios" id="nombre_usuario" placeholder="Ej. Juan_Perez" autocomplete="off" required>
                    <input type="hidden" name="id_user" id="id_user">

                    <datalist id="usuarios">
                        @foreach ($usuarios as $usuario)
                            <option
                                value="{{ $usuario->nombre_usuario }}"
                                data-userid="{{ $usuario->id_user }}"
                            ></option>
                        @endforeach
                    </datalist>
                @else
                    {{-- usa tu PK real (id_user) para no romper la relación --}}
                    <input type="hidden" name="id_user" value="{{ Auth::user()->id_user }}">
                @endif
            </div>

            {{-- Crédito activo con saldo > 0 --}}
            <div>
                <label for="id_credito">Seleccionar crédito</label>
                <select name="id_credito" id="id_credito" required>
                    <option value="">-- Selecciona un crédito --</option>
                    @foreach ($creditos->where('estado', 1)->filter(fn($c) => $c->saldo_total > 0) as $credito)
                        <option
                            value="{{ $credito->id_credito }}"
                            data-user="{{ $credito->id_user }}"
                        >
                            Crédito #{{ $credito->id_credito }} · {{ $credito->user->nombre_usuario ?? 'N/A' }} · ${{ number_format($credito->saldo_total, 2) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Monto --}}
        <div>
            <label for="monto_abono">Monto del abono</label>
            <input type="number" name="monto_abono" id="monto_abono" min="1" step="0.01" required>
        </div>

        <button type="submit">Aplicar al crédito</button>
    </form>
</div>
</body>
</html>
