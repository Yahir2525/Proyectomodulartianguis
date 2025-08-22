<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <link rel="stylesheet" href="{{ asset('css/pedido/createPedido.css') }}">
    <title>Crear Pedido</title>
</head>
<body>
    <h1>Crear nuevo pedido</h1>

    <form action="{{ url('/pedido') }}" method="POST">
        @csrf

        @if ($usuarios)
            {{-- Campo de búsqueda para seleccionar usuario --}}
            <label for="nombre_usuario">Buscar usuario:</label>
            <input list="usuarios" id="nombre_usuario" placeholder="Ej. Juan Pérez" required>
            <input type="hidden" name="id_user" id="id_user">

            <datalist id="usuarios">
                @foreach ($usuarios as $user)
                    <option value="{{ $user->nombre_usuario }}" data-userid="{{ $user->id_user }}"></option>
                @endforeach
            </datalist>

            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const input = document.getElementById('nombre_usuario');
                    const hidden = document.getElementById('id_user');
                    const opciones = document.getElementById('usuarios').options;

                    input.addEventListener('input', () => {
                        hidden.value = '';
                        for (let i = 0; i < opciones.length; i++) {
                            if (opciones[i].value === input.value) {
                                hidden.value = opciones[i].dataset.userid;
                                break;
                            }
                        }
                    });
                });
            </script>
        @else
            <input type="hidden" name="id_user" value="{{ $usuario->id_user }}">
        @endif

        <br><br>
        <button type="submit">Crear pedido</button>
    </form>

    <br>
    <a href="{{ url('/') }}">Inicio</a> |
    <a href="{{ url('/pedido') }}">Regresar</a>
</body>
</html>
