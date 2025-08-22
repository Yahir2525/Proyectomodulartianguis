<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <link rel="stylesheet" href="{{ asset('css/perfil/perfilindex.css') }}">
    <title>Mi Perfil</title>
</head>
<body>
<x-barraadmin></x-barraadmin>
<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Mi Perfil</h4>
        </div>
        <div class="card-body">

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @php $user = auth()->user(); @endphp

            <div class="text-center mb-4">
                @if (!empty($user->imagen))
                    <img src="{{ Storage::disk('s3')->url($user->imagen) }}" alt="Foto de perfil" class="img-thumbnail img-fluid" style="max-width:150px" loading="lazy">
                @else
                    <span>Sin imagen</span>
                @endif
            </div>

            <!-- Tabla responsiva -->
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-4">
                    <tbody>
                        <tr>
                            <th>Nombre completo</th>
                            <td>{{ $user->name }}</td>
                        </tr>
                        <tr>
                            <th>Correo electrónico</th>
                            <td>{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <th>Nombre de usuario</th>
                            <td>{{ $user->nombre_usuario }}</td>
                        </tr>
                        <tr>
                            <th>Género</th>
                            <td>
                                @switch($user->genero)
                                    @case('H') Hombre @break
                                    @case('M') Mujer @break
                                    @case('O') Otro @break
                                    @default No registrado
                                @endswitch
                            </td>
                        </tr>
                        <tr>
                            <th>Edad</th>
                            <td>{{ $user->edad ?? 'No registrada' }}</td>
                        </tr>
                        <tr>
                            <th>Teléfono</th>
                            <td>{{ $user->telefono ?? 'No registrado' }}</td>
                        </tr>
                        <tr>
                            <th>Dirección</th>
                            <td>{{ $user->direccion ?? 'No registrada' }}</td>
                        </tr>
                        <tr>
                            <th>Rol</th>
                            <td>{{ $user->getRoleNames()->implode(', ') }}</td>
                        </tr>
                        <tr>
                            <th>Nivel de usuario</th>
                            <td>{{ $user->nivel_usuario}}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="text-end">
                <a href="{{ route('perfil.editPerfil') }}" class="btn btn-warning w-100 w-sm-auto">Editar perfil</a>
            </div>

        </div>
    </div>
</div>

<!-- Bootstrap JS (opcional) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
