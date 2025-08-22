<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/sesion/login.css') }}">
    <title>Login</title>
    
</head>
<body>
<x-barrasesion/>
<section class="hero is-fullheight">
  <div class="hero-body">
    <div class="container">
      <div class="columns is-mobile is-centered">
        <!-- Columna responsiva: 12 en móvil, 8 en tablet, 5 en desktop, 4 en widescreen -->
        <div class="column is-12-mobile is-8-tablet is-5-desktop is-4-widescreen">
          
          <h1 class="title has-text-centered">Inicio de sesión</h1>
          <p class="subtitle has-text-centered has-text-grey">Por favor ingrese sus datos</p>

          @if (session('error'))
            <div class="notification is-danger is-light">
              {{ session('error') }}
            </div>
          @endif

          <div class="box">
            <form method="POST" action="{{ route('login') }}">
              @csrf

              <div class="field">
                <label class="label">Correo</label>
                <div>
                  <input class="input is-large" type="email" name="email" placeholder="Correo" value="{{ old('email') }}" required autofocus>
                </div>
                @error('email')
                  <p class="help is-danger">{{ $message }}</p>
                @enderror
              </div>

              <div class="field">
                <label class="label">Contraseña</label>
                <div>
                  <input class="input is-large" type="password" name="password" placeholder="Contraseña" required>
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

              <div class="field">
                <button type="submit" class="button is-info is-large is-fullwidth">
                  Conectarse
                </button>
              </div>

              <div class="field">
                <input class="button is-primary is-large is-fullwidth" type="reset" value="Limpiar datos">
              </div>
            </form>

            <div class="has-text-centered mt-4">
              <a href="/" class="mr-2">Inicio</a>
              <a href="{{ url('/registro') }}">Registrar</a>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</section>

<!-- (Opcional) Tu JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="js/scripts.js"></script>
</body>
</html>
