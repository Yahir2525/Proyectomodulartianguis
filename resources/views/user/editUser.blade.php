<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
</head>
<body>
<div class="container mt-5">
    <h1 class="title">Editar Usuario</h1>

    <div class="box">
        <form action="{{ url('/user', $user->id_user) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="field">
                <label class="label" for="name">Nombre:</label>
                <div class="control">
                    <input class="input" type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                </div>
                @error('name')
                    <p class="help is-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label class="label" for="nombre_usuario">Nombre de Usuario:</label>
                <div class="control">
                    <input class="input" type="text" id="nombre_usuario" name="nombre_usuario" value="{{ old('nombre_usuario', $user->nombre_usuario) }}" required>
                </div>
                @error('nombre_usuario')
                    <p class="help is-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label class="label" for="email">Correo:</label>
                <div class="control">
                    <input class="input" type="email" name="email" id="email" value="{{ old('email', $user->email) }}">
                </div>
                @error('email')
                    <p class="help is-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label class="label" for="password">Nueva Contraseña:</label>
                <div class="control">
                    <input class="input" type="password" name="password" id="password" placeholder="Escribe una nueva contraseña">
                </div>
                @error('password')
                    <p class="help is-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label class="label">Género:</label>
                <div class="control">
                    <label class="radio">
                        <input type="radio" name="genero" value="H" {{ $user->genero == 'H' ? 'checked' : '' }}> Hombre
                    </label>
                    <label class="radio">
                        <input type="radio" name="genero" value="M" {{ $user->genero == 'M' ? 'checked' : '' }}> Mujer
                    </label>
                    <label class="radio">
                        <input type="radio" name="genero" value="O" {{ $user->genero == 'O' ? 'checked' : '' }}> Otro
                    </label>
                </div>
                @error('genero')
                    <p class="help is-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label class="label" for="edad">Edad:</label>
                <div class="control">
                    <input class="input" type="number" name="edad" id="edad" min="0" value="{{ old('edad', $user->edad) }}">
                </div>
                @error('edad')
                    <p class="help is-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label class="label" for="telefono">Teléfono:</label>
                <div class="control">
                    <input class="input" type="text" name="telefono" id="telefono" value="{{ old('telefono', $user->telefono) }}">
                </div>
                @error('telefono')
                    <p class="help is-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label class="label" for="direccion">Dirección:</label>
                <div class="control">
                    <input class="input" type="text" name="direccion" id="direccion" value="{{ old('direccion', $user->direccion) }}">
                </div>
                @error('direccion')
                    <p class="help is-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label class="label" for="imagen">Imagen de perfil:</label>
                <div class="control">
                    <input class="input" type="file" name="imagen" id="imagen" accept="image/*">
                </div>
                @if ($user->imagen)
                    <figure class="mt-3">
                        <p class="label">Actual:</p>
                        <img src="{{ asset($user->imagen) }}" alt="Imagen actual" style="max-width: 150px;">
                    </figure>
                @endif
                @error('imagen')
                    <p class="help is-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label class="label" for="roles">Roles:</label>
                <div class="control">
                    <div class="select is-multiple">
                        <select name="roles[]" multiple>
                            @foreach ($roles as $role)
                                <option value="{{ $role }}" @if(in_array($role, old('roles', $user->getRoleNames()->toArray()))) selected @endif>
                                    {{ $role }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @error('roles')
                    <p class="help is-danger">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="nivel_usuario">Nivel del usuario:</label>
                <select name="nivel_usuario" id="nivel_usuario" required>
                    <option value="excelente" {{ $user->nivel_usuario == 'excelente' ? 'selected' : '' }}>Excelente cliente</option>
                    <option value="bueno" {{ $user->nivel_usuario == 'bueno' ? 'selected' : '' }}>Buen cliente</option>
                    <option value="malo" {{ $user->nivel_usuario == 'malo' ? 'selected' : '' }}>Mal cliente</option>
                </select>
            </div>

            <div class="field mt-4">
                <div class="control">
                    <button class="button is-info is-fullwidth" type="submit">Guardar cambios</button>
                </div>
            </div>
        </form>
    </div>
</div>
</body>
</html>
