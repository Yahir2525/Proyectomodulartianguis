<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/sesion/login.css') }}">
    <title>Iniciar sesión</title>
</head>
<body>
<div class="page-container">
<main class="content">
<br><x-barrasesion/>

    <section class="hero is-fullheight d-flex justify-content-center align-items-center">
        <div class="login-card text-center">
            <h2 class="title mb-3">Inicio de sesión</h2>
            <p class="subtitle mb-4">Por favor ingrese sus datos</p>

            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email -->
                <div class="field mb-3 text-start">
                    <label class="label">Correo</label>
                    <input class="form-control" type="email" name="email" placeholder="Correo"
                           value="{{ old('email') }}" required autofocus>
                    @error('email')
                        <p class="text-danger small mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div class="field mb-3 text-start">
                    <label class="label">Contraseña</label>
                    <input class="form-control" type="password" name="password" placeholder="Contraseña" required>
                    @error('password')
                        <p class="text-danger small mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember -->
                <div class="form-check mb-4 text-start">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label for="remember" class="form-check-label">Recordarme</label>
                </div>

                <!-- Buttons -->
                <div class="mb-3">
                    <button type="submit" class="btn btn-info btn-lg w-100">
                        Conectarse
                    </button>
                </div>
                <div class="mb-3">
                    <input class="btn btn-primary btn-lg w-100" type="reset" value="Limpiar datos">
                </div>
            </form>

            <!-- Links -->
            <div class="login-links mt-4">
                <a href="/" class="me-3">Inicio</a>
                <a href="{{ url('/registro') }}">Registrar</a>
            </div>
        </div>
    </section>
</main>
<x-footer/>
</div>
</body>
</html>
