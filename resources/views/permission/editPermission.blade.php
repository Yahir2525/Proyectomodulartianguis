<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/permission/editPermission.css') }}">
  <title>Editar permiso</title>
</head>
<body>
<div class="page-container">
<main class="content">
<br><x-barracreate/>
  <div class="container">
    <br><hr class="hr-grueso"><center><h1>Editar permiso</h1></center><hr class="hr-grueso"><br>

    {{-- Mensajes --}}
    @if (session('success'))
      <div class="alert success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
      <div class="alert danger">
        <ul class="mb-0">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('permission.update', $permission->id) }}" class="card">
      @csrf
      @method('PUT')
      <div class="form-row">
        <label for="name">Nombre del permiso</label>
        <input id="name" name="name" type="text" required
               value="{{ old('name', $permission->name) }}" placeholder="Ej. editar usuarios">
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-primary">Guardar cambios</button>
        <br><a href="{{ route('permission.index') }}" class="btn btn-danger">Cancelar</a>
      </div>
    </form>
  </div>
</main>
<x-footer/>
</div>
</body>
</html>
