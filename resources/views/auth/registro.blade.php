<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="{{ asset('css/sesion/registro.css') }}">
  <title>Registro de Usuario</title>
</head>
<body>
    <div class="page-container">
        <br><x-barrasesion/>
        <main class="content"><br><br>
            <section class="hero is-fullheight d-flex justify-content-center align-items-center">
                <div class="registro-card text-center">
                    <h2 class="title mb-3">Registro de usuario</h2>
                        <p class="subtitle mb-4" style="color: black">Complete sus datos para crear una cuenta</p>

                        @if ($errors->any())
                            <div class="alert alert-danger text-start">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            </div>
                        @endif

                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        <form action="{{ route('registro.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <!-- Nombre -->
                            <div class="field mb-3 text-start fw-bold">
                            <label class="label">Nombre Completo</label>
                            <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
                            </div>

                            <!-- Nombre usuario -->
                            <div class="field mb-3 text-start fw-bold">
                            <label class="label">Nombre de usuario</label>
                            <input type="text" name="nombre_usuario" class="form-control" required value="{{ old('nombre_usuario') }}">
                            </div>

                            <!-- Email -->
                            <div class="field mb-3 text-start fw-bold">
                            <label class="label">Correo Electrónico</label>
                            <input type="email" name="email" class="form-control" required value="{{ old('email') }}">
                            </div>

                            <!-- Contraseña -->
                            <div class="field mb-3 text-start fw-bold">
                            <label class="label">Contraseña</label>
                            <input type="password" id="password" name="password" class="form-control" required>
                            <ul class="form-text mt-2 ps-3" style="font-size: 0.9rem; color: blue;">
                                <li>Mínimo 8 y máximo 20 caracteres</li>
                                <li>Al menos una letra minúscula (a-z)</li>
                                <li>Al menos una letra mayúscula (A-Z)</li>
                                <li>Al menos un número (0-9)</li>
                                <li>Al menos un carácter especial (@$!%*?&)</li>
                            </ul>
                            </div>

                            <!-- Confirmar -->
                            <div class="field mb-4 text-start fw-bold">
                            <label class="label">Confirmar Contraseña</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                            <small id="message" class="form-text"></small>
                            </div>

                            <!-- Mostrar contraseñas -->
                            <div class="form-check mb-3 text-start">
                            <input class="form-check-input" type="checkbox" id="showPassword">
                            <label class="form-check-label" for="showPassword">
                                <i class="fa-solid fa-eye"></i> Mostrar contraseñas
                            </label>
                            </div>


                            <!-- Botones -->
                            <div class="mb-3">
                            <button type="submit" class="btn btn-success btn-lg w-100">
                                Registrarse
                            </button>
                            </div>
                            <div class="mb-3">
                            <input type="reset" class="btn btn-secondary btn-lg w-100" value="Limpiar datos">
                            </div>
                        </form>

                        <div class="registro-links mt-4">
                            <a href="/" class="me-3">Inicio</a>
                            <a href="{{ url('/login') }}">Iniciar sesión</a>
                        </div>
                </div>
            </section>
        </main>
        <x-footer/>
        <script>
            const password = document.getElementById("password");
            const confirmPassword = document.getElementById("password_confirmation");
            const message = document.getElementById("message");

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

            document.getElementById('showPassword').addEventListener('change', function() {
                [password, confirmPassword].forEach(field => {
                    field.type = this.checked ? 'text' : 'password';
                });
            });
        </script>
    </div>
</body>
</html>
