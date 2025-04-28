<!DOCTYPE html>
<html lang="en">
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

    <form action="{{ url('/user', $user->id_user) }}" method="POST">
    @csrf
    @method('PUT')
        <div class="field">
            <label class="label" for="name">Nombre:</label>
            <div class="control">
                <input class="input" type="text" id="name" name="name" value="{{ $user->name }}">
            </div>
            @error('name')
                    <p class="help is-danger">{{ $message }}</p>
            @enderror
        </div>
        <div class="field">
                <label class="label" for="email">Correo</label>
                <div class="control">
                    <input class="input" type="email" name="email" id="email" value="{{ $user->email }}" required>
                </div>
                @error('email')
                    <p class="help is-danger">{{ $message }}</p>
                @enderror
        </div>
        <div class="field">
            <label class="label" for="password">Nueva Contraseña</label>
            <div class="control">
                <input class="input" type="password" name="password" id="password" placeholder="Escribe una nueva contraseña">
            </div>
            @error('password')
                <p class="help is-danger">{{ $message }}</p>
            @enderror
        </div>
        <div class="field">
                <label class="label">Género</label>
                <div class="control">
                <label class="radio">
                    <input type="radio" name="genero" value="H" {{ $user->genero == 'H' ? 'checked' : '' }} required> Hombre
                </label>
                <label class="radio">
                    <input type="radio" name="genero" value="M" {{ $user->genero == 'M' ? 'checked' : '' }} required> Mujer
                </label>
                <label class="radio">
                    <input type="radio" name="genero" value="O" {{ $user->genero == 'O' ? 'checked' : '' }} required> Otro
                </label>
                </div>
                @error('genero')
                    <p class="help is-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label class="label" for="edad">Edad</label>
                <div class="control">
                    <input class="input" type="number" name="edad" id="edad" value="{{ $user->edad }}" required min="0">
                </div>
                @error('edad')
                    <p class="help is-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label class="label" for="telefono">Teléfono</label>
                <div class="control">
                    <input class="input" type="text" name="telefono" id="telefono" value="{{ $user->telefono }}" required>
                </div>
                @error('telefono')
                    <p class="help is-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label class="label" for="direccion">Dirección</label>
                <div class="control">
                    <input class="input" type="text" name="direccion" id="direccion" value="{{ $user->direccion }}" required>
                </div>
                @error('direccion')
                    <p class="help is-danger">{{ $message }}</p>
                @enderror
            </div>
            <div class="field">
                <label class="label" for="roles">Roles</label>
                <div class="control">
                    <div class="select is-multiple">
                        <select name="roles[]" multiple>
                            @foreach ($roles as $role)
                                <option value="{{ $role }}" 
                                    @if(in_array($role, old('roles', $user->getRoleNames()->toArray()))) selected @endif>
                                    {{ $role }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
        <div class="field">
            <div class="control">
                <button class="button is-block is-info is-large is-fullwidth" type="submit">Guardar cambios</button>
            </div>
        </div>
    </form>




    </div>
</div>
</body>
</html>
