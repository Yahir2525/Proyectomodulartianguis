<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuario</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container min-vh-100 d-flex align-items-center py-4">
  <div class="row justify-content-center w-100">
    <div class="col-12 col-sm-10 col-md-8 col-lg-6">
      <div class="card shadow-sm">
        <div class="card-header text-center">Registro de Usuario</div>
        <div class="card-body">

          @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
          @endif

          <form action="{{ route('registro.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            @if ($errors->any())
              <div class="alert alert-danger">
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

            <div class="mb-3">
              <label for="imagen" class="form-label">Foto de perfil</label>
              <input id="imagen" type="file" name="imagen" accept="image/*" class="form-control">
            </div>

            <div class="mb-3">
              <label class="form-label">Nombre Completo</label>
              <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
            </div>

            <div class="mb-3">
              <label for="nombre_usuario" class="form-label">Nombre de usuario</label>
              <input id="nombre_usuario" type="text" name="nombre_usuario" class="form-control" required value="{{ old('nombre_usuario') }}">
            </div>

            <div class="mb-3">
              <label class="form-label">Correo Electrónico</label>
              <input type="email" name="email" class="form-control" required value="{{ old('email') }}">
            </div>

            <div class="mb-3">
              <label class="form-label">Contraseña</label>
              <input type="password" name="password" class="form-control" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Confirmar Contraseña</label>
              <input type="password" name="password_confirmation" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Registrarse</button>
          </form>

          <div class="text-center mt-3">
            <a href="/">Inicio</a><br>
            <a href="{{ url('/login') }}">Iniciar sesión</a>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
