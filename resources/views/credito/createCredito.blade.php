<!-- <!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/credito/createCredito.css') }}">
    <title>Crear Crédito</title>
</head>
<body>
<br>
    <br><hr class="hr-grueso"><center><h1>Crear nuevo credito</h1></center><hr class="hr-grueso"><br>

    @if ($errors->any())
        <div style="color: red;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('credito.store') }}" method="POST">
        @csrf

        @if(Auth::user()->hasRole('administrador'))
            <label for="nombre_usuario">Buscar usuario:</label>
            <input list="usuarios" id="nombre_usuario" placeholder="Ej. Juan Pérez" required>
            <input type="hidden" name="id_user" id="id_user">

            <datalist id="usuarios">
                @foreach ($usuarios as $usuario)
                    <option value="{{ $usuario->nombre_usuario }}" data-userid="{{ $usuario->id_user }}"></option>
                @endforeach
            </datalist>

            <br><br>
            <p id="mensaje-restriccion" style="color: red;"></p>

            <script>
                const restricciones = @json($datosRestricciones);

                function validarRestricciones(userId) {
                    const boton = document.getElementById('btnCrear');
                    const mensaje = document.getElementById('mensaje-restriccion');

                    if (!restricciones[userId]) {
                        boton.disabled = true;
                        mensaje.innerText = '';
                        return;
                    }

                    const { activos, suma } = restricciones[userId];

                    if (activos >= 3) {
                        boton.disabled = true;
                        mensaje.innerText = 'Este usuario ya tiene 3 créditos activos.';
                    } else if (suma >= 10000) {
                        boton.disabled = true;
                        mensaje.innerText = 'La suma de los saldos activos supera los $10,000.';
                    } else {
                        boton.disabled = false;
                        mensaje.innerText = '';
                    }
                }

                document.addEventListener('DOMContentLoaded', () => {
                    const input = document.getElementById('nombre_usuario');
                    const hidden = document.getElementById('id_user');
                    const opciones = document.getElementById('usuarios').options;

                    input.addEventListener('input', () => {
                        hidden.value = '';
                        for (let i = 0; i < opciones.length; i++) {
                            if (opciones[i].value === input.value) {
                                const userId = opciones[i].dataset.userid;
                                hidden.value = userId;
                                validarRestricciones(userId);
                                break;
                            }
                        }

                        // Si no se encuentra coincidencia
                        if (!hidden.value) {
                            document.getElementById('btnCrear').disabled = true;
                            document.getElementById('mensaje-restriccion').innerText = '';
                        }
                    });
                });
            </script>
        @else
            <input type="hidden" name="id_user" value="{{ Auth::id() }}">
            <p><strong>Usuario:</strong> {{ Auth::user()->nombre_usuario }}</p>
        @endif

        <br><br>
        <button type="submit" id="btnCrear" {{ Auth::user()->hasRole('administrador') ? 'disabled' : '' }}>Crear crédito</button>
    </form>

    <br>
    <a href="{{ url('/credito') }}">Volver al listado</a>
</body>
</html> -->
