<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Crédito</title>
</head>
<body>
    <h1>Registrar nuevo crédito</h1>

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
            <label for="id_user">Seleccionar usuario:</label>
            <select name="id_user" id="id_user" onchange="validarRestricciones()" required>
                <option value="">-- Selecciona un usuario --</option>
                @foreach ($usuarios as $usuario)
                    <option value="{{ $usuario->id_user }}">{{ $usuario->nombre_usuario }}</option>
                @endforeach
            </select>
        @else
            <input type="hidden" name="id_user" value="{{ Auth::id() }}">
            <p><strong>Usuario:</strong> {{ Auth::user()->nombre_usuario }}</p>
        @endif

        <br><br>
        <p id="mensaje-restriccion" style="color: red;"></p>

        <button type="submit" id="btnCrear" {{ Auth::user()->hasRole('administrador') ? 'disabled' : '' }}>Crear crédito</button>
    </form>

    <br>
    <a href="{{ url('/credito') }}">Volver al listado</a>

    @if(Auth::user()->hasRole('administrador'))
    <script>
        const restricciones = @json($datosRestricciones);

        function validarRestricciones() {
            const userId = document.getElementById('id_user').value;
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
    </script>
    @endif
</body>
</html>
