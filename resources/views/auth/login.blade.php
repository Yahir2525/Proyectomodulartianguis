<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="Inicio de sesión" />
    <meta name="author" content="Juan y Yahir" />
    <title>Login</title>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css" rel="stylesheet">
</head>
<body class="bg-primary">
<div class="hero-body">
    <div class="container has-text-centered">
        <div class="column is-4 is-offset-4">
            <h1>Inicio de sesión</h1>
            <hr class="login-hr">
            <p class="subtitle has-text-black">Por favor ingrese sus datos</p>

            <!-- Mensaje de error si hay problemas de autenticación -->
            @if (session('error'))
                <div class="notification is-danger">
                    {{ session('error') }}
                </div>
            @endif

            <div class="box">
                <!-- Formulario de login -->
                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="field">
                        <div class="control">
                            <input class="input is-large" type="email" name="email" placeholder="Correo:" value="{{ old('email') }}" required autofocus>
                        </div>
                        @error('email')
                            <p class="help is-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="field">
                        <div class="control">
                            <input class="input is-large" type="password" name="password" placeholder="Contraseña:" required>
                        </div>
                        @error('password')
                            <p class="help is-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="field">
                        <label class="checkbox">
                            <input type="checkbox" name="remember">
                            Recordarme
                        </label>
                    </div>

                    <button type="submit" class="button is-block is-info is-large is-fullwidth">
                        Conectarse
                    </button>

                    <br>

                    <input class="button is-block is-primary is-large is-fullwidth" type="reset" value="Limpiar datos">

                </form>

                <br>
                <div>
                    <a href="/">Inicio</a><br>
                    <a href="{{ url('/registro') }}">Registrar</a>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="js/scripts.js"></script>
</body>
</html>
