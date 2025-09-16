<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/abono/createAbono.css') }}">
    <title>Crear abono</title>
    <script>
        function filtrarCreditos() {
            const usuarioInput = document.getElementById('nombre_usuario');
            const userIdInput  = document.getElementById('id_user');
            const selectedName = usuarioInput.value.trim();
            const options      = usuarioInput.list ? usuarioInput.list.options : [];
            let userId = '';

            for (let i = 0; i < options.length; i++) {
                if (options[i].value === selectedName) {
                    userId = options[i].dataset.userid || '';
                    break;
                }
            }
            if (userIdInput) userIdInput.value = userId;

            const creditos = document.querySelectorAll('#id_credito option');
            creditos.forEach(op => {
                if (!op.value) { op.hidden = false; return; }
                const opUser = op.dataset.user || '';
                op.hidden = (userId !== '' && opUser !== userId);
            });

            const select = document.getElementById('id_credito');
            if (select) select.value = '';
        }

        document.addEventListener("DOMContentLoaded", () => {
            const usuarioInput = document.getElementById('nombre_usuario');
            if (usuarioInput) {
                usuarioInput.addEventListener('change', filtrarCreditos);
                filtrarCreditos();
            }
        });
    </script>
</head>
<body>
    <div class="page-container">
        <main class="content">
        <br><x-barracreate/>
            <div class="container">
                <br><hr class="hr-grueso"><center><h1>Registrar nuevo abono</h1></center><hr class="hr-grueso"><br>

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
                                <input type="hidden" name="id_user" value="{{ Auth::user()->id_user }}">
                            @endif
                        </div><br>
                        
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
                    </div>

                    <div>
                        <label for="monto_abono">Monto del abono</label>
                        <input type="number" name="monto_abono" id="monto_abono" min="1" step="0.01" required>
                    </div>

                    <button type="submit">Aplicar al crédito</button>

                </form><br>
    
                <center><div class="back-wrap">
                    <a href="{{ route('abono.index') }}" class="btn btn-danger">Cancelar</a>
                </div></center>
            </div>
        </main>
        <x-footer/>
    </div>
</body>
</html>
