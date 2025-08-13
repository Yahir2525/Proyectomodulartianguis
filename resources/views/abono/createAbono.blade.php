<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Abono</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/abono/createAbono.css') }}">
    
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
    <br><hr><center><h1>Registrar nuevo abono</h1></center><hr><br>

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
            </div> <br>

            {{-- Crédito activo con saldo > 0 --}}
            <div>
                <label for="id_credito">Seleccionar crédito</label>
                <select name="id_credito" id="id_credito" required>
                    <option value="">-- Selecciona un crédito --</option>
                    @foreach ($creditos->where('estado', 1)->filter(fn($c) => $c->saldo_total > 0) as $credito)
                        <option
                            value="{{ $credito->id_credito }}"
                            data-user="{{ $credito->id_user }}">
                            Crédito #{{ $credito->id_credito }} · {{ $credito->user->nombre_usuario ?? 'N/A' }} · ${{ number_format($credito->saldo_total, 2) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div><br>

        {{-- Monto --}}
        <div>
            <label for="monto_abono">Monto del abono</label>
            <input type="number" name="monto_abono" id="monto_abono" min="1" step="0.01" required>
        </div>
        <br><button type="submit">Aplicar al crédito</button>
    </form><br><br>
    <center>
    <div class="back-wrap">
    <a href="{{ route('abono.index') }}" class="btn btn-primary">Cancelar</a>
    </div></center>
</div>
</body>
</html>
