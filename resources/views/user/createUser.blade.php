<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/user/createUser.css') }}">
    <title>Crear usuarios</title>
</head>
<body>
<div class="page-container">
<main class="content">
<br>
<div class="container mt-5">
    <br><hr class="hr-grueso"><center><h1>Principal de créditos</h1></center><hr class="hr-grueso"><br>
        <div class="row">
            <div class="col-md-12">

                @if ($errors->any())
                <ul class="alert alert-warning">
                    @foreach ($errors->all() as $error)
                        <li>{{$error}}</li>
                    @endforeach
                </ul>
                @endif
                    <div class="card-body">
                    <form action="{{ url('/user') }}" method="POST" enctype="multipart/form-data"> 
                            @csrf

                            <<div class="field">
                            <label class="label" for="name">Nombre</label>
                            <div class="control">
                                <input class="input" type="text" name="name" id="name" value="{{ old('name') }}" required>
                            </div>
                            @error('name')
                                <p class="help is-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="field">
                            <label class="label" for="email">Correo</label>
                            <div class="control">
                                <input class="input" type="email" name="email" id="email" value="{{ old('email') }}" required>
                            </div>
                            @error('email')
                                <p class="help is-danger">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-3">
                                <label for="">Password</label>
                                <input type="text" name="password" class="form-control" />
                        </div>
                        <div class="field">
                        <label class="form-label">Género</label><br>
                        <div>
                            <input type="radio" id="generoH" name="genero" value="H" required>
                            <label for="generoH">Hombre</label>
                        </div>
                        <div>
                            <input type="radio" id="generoM" name="genero" value="M" required>
                            <label for="generoM">Mujer</label>
                        </div>
                        <div>
                            <input type="radio" id="generoU" name="genero" value="U" required>
                            <label for="generoU">Otro</label>
                        </div>
                            @error('genero')
                                <p class="help is-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="field">
                            <label class="label" for="edad">Edad</label>
                            <div class="control">
                                <input class="input" type="number" name="edad" id="edad" value="{{ old('edad') }}" required min="0">
                            </div>
                            @error('edad')
                                <p class="help is-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="field">
                            <label class="label" for="telefono">Teléfono</label>
                            <div class="control">
                                <input class="input" type="text" name="telefono" id="telefono" value="{{ old('telefono') }}" required>
                            </div>
                            @error('telefono')
                                <p class="help is-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="field">
                            <label class="label" for="direccion">Dirección</label>
                            <div class="control">
                                <input class="input" type="text" name="direccion" id="direccion" value="{{ old('direccion') }}" required>
                            </div>
                            @error('direccion')
                                <p class="help is-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="field">
                            <label class="label" for="nombre_usuario">Nombre de Usuario</label>
                            <div class="control">
                                <input class="input" type="text" name="nombre_usuario" id="nombre_usuario" value="{{ old('nombre_usuario') }}" required>
                            </div>
                            @error('nombre_usuario')
                                <p class="help is-danger">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="imagen">Foto de perfil:</label>
                            <input type="file" name="imagen" id="imagen" accept="image/*">
                            @if (isset($user) && !empty($user->imagen_url))
                                <div class="mt-2">
                                    <img src="{{ $user->imagen_url }}" alt="Foto seleccionada"
                                        class="img-thumbnail img-fluid" style="max-width:150px" loading="lazy">
                                </div>
                            @endif
                        </div> <br><br>
                        <div class="mb-3">
                            <label for="">Roles</label>
                            <select name="roles[]" class="form-control" multiple>
                                <option value="">Select Role</option>
                                @foreach ($roles as $role)
                                <option value="{{ $role }}">{{ $role }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="control">
                        <button class="button is-block is-info is-large is-fullwidth" type="submit">
                            Enviar</button>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<x-footer/>
</div>
</body>
</html>