<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ asset('css/perfil/editPerfil.css') }}">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Editar Perfil</title>
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-warning text-dark">
            <h4 class="mb-0">Editar Perfil</h4>
        </div>
        <div class="card-body">

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('perfil.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')


                <div class="field">
                <label class="label" for="imagen">Imagen de perfil:</label>
                <div class="control">
                    <input class="input" type="file" name="imagen" id="imagen" accept="image/*">
                </div>
                    @if (!empty($user->imagen_url))
                        <figure class="mt-3">
                            <p class="label">Actual:</p>
                            <img src="{{ $user->imagen_url }}" alt="Imagen actual" style="max-width: 150px;">
                        </figure>
                    @endif
                    @error('imagen')
                        <p class="help is-danger">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="name" class="form-label">Nombre completo</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', auth()->user()->name) }}" required>
                </div>

                <div class="mb-3">
                    <label for="nombre_usuario" class="form-label">Nombre de usuario</label>
                    <input type="text" name="nombre_usuario" id="nombre_usuario" class="form-control" value="{{ old('nombre_usuario', auth()->user()->nombre_usuario) }}" required>
                </div>

                <div class="mb-3">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="text" name="telefono" id="telefono" class="form-control" value="{{ old('telefono', auth()->user()->telefono) }}">
                </div>

                <div class="mb-3">
                    <label for="direccion" class="form-label">Dirección</label>
                    <input type="text" name="direccion" id="direccion" class="form-control" value="{{ old('direccion', auth()->user()->direccion) }}">
                </div>

                <div class="mb-3">
                    <label for="edad" class="form-label">Edad</label>
                    <input type="number" name="edad" id="edad" class="form-control" min="0" value="{{ old('edad', auth()->user()->edad) }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Género</label><br>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="genero" value="H" id="generoH" {{ old('genero', auth()->user()->genero) == 'H' ? 'checked' : '' }}>
                        <label class="form-check-label" for="generoH">Hombre</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="genero" value="M" id="generoM" {{ old('genero', auth()->user()->genero) == 'M' ? 'checked' : '' }}>
                        <label class="form-check-label" for="generoM">Mujer</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="genero" value="O" id="generoO" {{ old('genero', auth()->user()->genero) == 'O' ? 'checked' : '' }}>
                        <label class="form-check-label" for="generoO">Otro</label>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Correo electrónico</label>
                    <input type="email" name="email" id="email" class="form-control"
                        value="{{ old('email', auth()->user()->email) }}">
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Nueva contraseña</label>
                    <input type="password" name="password" id="password" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                </div>


                <div class="text-end">
                    
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                </div>
            </form>

        </div>
    </div>
</div>

<!-- Bootstrap JS (opcional) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
