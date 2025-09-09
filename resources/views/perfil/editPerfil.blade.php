<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/perfil/editPerfil.css') }}">
    <title>Editar Perfil</title>
</head>
<body>
<div class="page-container">
<main class="content">
<br><x-barracreate/>
<div class="container mt-5">
    <div class="container mt-5">
    <div class="profile-edit-card shadow">
        <div class="profile-edit-header">
            <h2>Editar perfil</h2>
            <p class="subtitle">Mantén tu información actualizada</p>
        </div>

        <div class="profile-edit-body">
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
                <!-- Imagen -->
                <div class="mb-5 text-center">
                    <h5 class="section-title"><i class="fa-solid fa-image"></i> Imagen de perfil</h5>
                    <input type="file" class="form-control w-50 mx-auto" name="imagen" id="imagen" accept="image/*">
                    @if (!empty($user->imagen_url))
                        <div class="mt-3">
                            <small class="text-muted">Actual:</small><br>
                            <img src="{{ $user->imagen_url }}" alt="Imagen actual" class="rounded shadow-sm mt-2" style="max-width: 120px;">
                        </div>
                    @endif
                </div>

                <!-- Sección: Datos personales -->
                <h5 class="section-title"><i class="fa-solid fa-id-card"></i> Datos personales</h5>
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Nombre completo</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                            <input type="text" class="form-control" name="name" value="{{ old('name', $user->name) }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nombre de usuario</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-at"></i></span>
                            <input type="text" class="form-control" name="nombre_usuario" value="{{ old('nombre_usuario', $user->nombre_usuario) }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Teléfono</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-phone"></i></span>
                            <input type="text" class="form-control" name="telefono" value="{{ old('telefono', $user->telefono) }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Dirección</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-location-dot"></i></span>
                            <input type="text" class="form-control" name="direccion" value="{{ old('direccion', $user->direccion) }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Edad</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-cake-candles"></i></span>
                            <input type="number" class="form-control" name="edad" min="0" value="{{ old('edad', $user->edad) }}">
                        </div>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Género</label>
                        <div class="d-flex gap-4 align-items-center mt-1">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="genero" value="H" {{ old('genero', $user->genero) == 'H' ? 'checked' : '' }}>
                                <label class="form-check-label"><i class="fa-solid fa-mars"></i> Hombre</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="genero" value="M" {{ old('genero', $user->genero) == 'M' ? 'checked' : '' }}>
                                <label class="form-check-label"><i class="fa-solid fa-venus"></i> Mujer</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="genero" value="O" {{ old('genero', $user->genero) == 'O' ? 'checked' : '' }}>
                                <label class="form-check-label"><i class="fa-solid fa-genderless"></i> Otro</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección: Cuenta -->
                <h5 class="section-title"><i class="fa-solid fa-envelope"></i> Cuenta</h5>
                <div class="row g-4 mb-4">
                    <div class="col-12">
                        <label class="form-label">Correo electrónico</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                            <input type="email" class="form-control" name="email" value="{{ old('email', $user->email) }}">
                        </div>
                    </div>
                </div>

                <!-- Sección: Seguridad -->
                <!-- Sección: Seguridad -->
                <h5 class="section-title"><i class="fa-solid fa-lock"></i> Seguridad</h5>
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Nueva contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-key"></i></span>
                            <input type="password" id="password" class="form-control" name="password">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirmar contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-key"></i></span>
                            <input type="password" id="confirm_password" class="form-control" name="password_confirmation">
                        </div>
                        <!-- Mensaje dinámico -->
                        <small id="message" class="form-text"></small>
                    </div>
                    <div class="col-12 mt-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="showPassword">
                            <label class="form-check-label" for="showPassword">
                                Mostrar contraseñas
                            </label>
                        </div>
                    </div>
                </div>


                <!-- Botones -->
                <div class="text-center mt-4">
                    <button type="submit" class="btn-save">Guardar cambios</button>
                </div>
                <div class="text-center mt-3">
                    <a href="{{ route('perfil.perfilIndex') }}" class="btn-cancel"> Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

</div>

<script>
const password = document.getElementById("password");
const confirmPassword = document.getElementById("confirm_password");
const message = document.getElementById("message");

// Validación en tiempo real
function checkPasswords() {
    if (confirmPassword.value === "") {
        message.textContent = "";
        confirmPassword.style.borderColor = "";
        return;
    }
    if (password.value === confirmPassword.value) {
        confirmPassword.classList.add("is-valid");
        confirmPassword.classList.remove("is-invalid");
        message.textContent = "✔ Contraseñas coinciden";
    } else {
        confirmPassword.classList.add("is-invalid");
        confirmPassword.classList.remove("is-valid");
        message.textContent = "✘ Contraseñas no coinciden";
    }
}

password.addEventListener("keyup", checkPasswords);
confirmPassword.addEventListener("keyup", checkPasswords);

document.querySelector("form").addEventListener("submit", function(e) {
    if (password.value !== confirmPassword.value) {
        e.preventDefault();
        alert("Las contraseñas no coinciden");
        confirmPassword.focus();
    }
});

// Mostrar/Ocultar contraseñas
document.getElementById('showPassword').addEventListener('change', function() {
    const passwordFields = [password, confirmPassword];
    passwordFields.forEach(field => {
        field.type = this.checked ? 'text' : 'password';
    });
});
</script>
<!-- Bootstrap JS (opcional) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</main>
<x-footer/>
</div>
</body>
</html>
