<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/user/createUser.css') }}">
    <title>Crear usuario</title>
</head>
<body>
<div class="page-container">
<main class="content">
<br><x-barracreate/>
<div class="container mt-5">
    <div class="profile-edit-card shadow">
        <!-- Cabecera -->
        <div class="profile-edit-header">
            <h2>Crear usuario</h2>
            <p class="subtitle">Registra un nuevo usuario en el sistema</p>
        </div>

        <!-- Cuerpo -->
        <div class="profile-edit-body">
            <form action="{{ url('/user') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Imagen -->
                <h5 class="section-title"><i class="fa-solid fa-image"></i> Imagen de perfil</h5>
                <div class="mb-4 text-center">
                    <input type="file" class="form-control w-50 mx-auto" name="imagen" accept="image/*">
                </div>

                <!-- Datos personales -->
                <h5 class="section-title"><i class="fa-solid fa-id-card"></i> Datos personales</h5>
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Nombre completo</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                            <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nombre de usuario</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-at"></i></span>
                            <input type="text" class="form-control" name="nombre_usuario" value="{{ old('nombre_usuario') }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Teléfono</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-phone"></i></span>
                            <input type="text" class="form-control" name="telefono" value="{{ old('telefono') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Dirección</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-location-dot"></i></span>
                            <input type="text" class="form-control" name="direccion" value="{{ old('direccion') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Edad</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-cake-candles"></i></span>
                            <input type="number" class="form-control" name="edad" min="0" value="{{ old('edad') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Género</label>
                        <div class="d-flex gap-4 align-items-center mt-1">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="genero" value="H" {{ old('genero') == 'H' ? 'checked' : '' }}>
                                <label class="form-check-label"><i class="fa-solid fa-mars"></i> Hombre</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="genero" value="M" {{ old('genero') == 'M' ? 'checked' : '' }}>
                                <label class="form-check-label"><i class="fa-solid fa-venus"></i> Mujer</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="genero" value="O" {{ old('genero') == 'O' ? 'checked' : '' }}>
                                <label class="form-check-label"><i class="fa-solid fa-genderless"></i> Otro</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cuenta -->
                <h5 class="section-title"><i class="fa-solid fa-envelope"></i> Cuenta</h5>
                <div class="row g-4 mb-4">
                    <div class="col-12">
                        <label class="form-label">Correo electrónico</label>
                        <input type="email" class="form-control" name="email" value="{{ old('email') }}" required>
                    </div>
                </div>

                <!-- Seguridad -->
                <h5 class="section-title"><i class="fa-solid fa-lock"></i> Seguridad y permisos</h5>
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Contraseña</label>
                        <input type="password" id="password" class="form-control" name="password" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirmar contraseña</label>
                        <input type="password" id="password_confirmation" class="form-control" name="password_confirmation" required>
                        <small id="message" class="form-text"></small>
                    </div>
                    <div class="col-12 mt-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="showPassword">
                            <label class="form-check-label" for="showPassword">
                                <i class="fa-solid fa-eye"></i> Mostrar contraseñas
                            </label>
                        </div>
                    </div>

                    <!-- Roles y Nivel -->
                    <div class="col-md-6">
                        <label class="form-label">Rol</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-user-shield"></i></span>
                            <select class="form-select" name="roles" required>
                                @foreach ($roles as $role)
                                    <option value="{{ $role }}" {{ old('roles') == $role ? 'selected' : '' }}>
                                        {{ ucfirst($role) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Nivel del usuario</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-star"></i></span>
                            <select class="form-select" name="nivel_usuario" required>
                                <option value="excelente" {{ old('nivel_usuario') == 'excelente' ? 'selected' : '' }}>Excelente cliente</option>
                                <option value="bueno" {{ old('nivel_usuario') == 'bueno' ? 'selected' : '' }}>Buen cliente</option>
                                <option value="malo" {{ old('nivel_usuario') == 'malo' ? 'selected' : '' }}>Mal cliente</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <div class="text-center mt-4">
                    <button type="submit" class="btn-save">Crear usuario</button>
                </div>
                <div class="text-center mt-3">
                    <a href="{{ route('user.index') }}" class="btn-cancel">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

</main>
<x-footer/>
</div>

<script>
const password = document.getElementById("password");
const confirmPassword = document.getElementById("password_confirmation");
const message = document.getElementById("message");

// Validación en tiempo real
function checkPasswords() {
    if (confirmPassword.value === "") {
        message.textContent = "";
        confirmPassword.style.borderColor = "";
        return;
    }
    if (password.value === confirmPassword.value) {
        confirmPassword.style.borderColor = "green";
        message.textContent = "✔ Contraseñas coinciden";
        message.style.color = "green";
    } else {
        confirmPassword.style.borderColor = "red";
        message.textContent = "✘ Contraseñas no coinciden";
        message.style.color = "red";
    }
}
password.addEventListener("keyup", checkPasswords);
confirmPassword.addEventListener("keyup", checkPasswords);

// Mostrar/ocultar contraseñas
document.getElementById('showPassword').addEventListener('change', function() {
    [password, confirmPassword].forEach(field => {
        field.type = this.checked ? 'text' : 'password';
    });
});
</script>
</body>
</html>
