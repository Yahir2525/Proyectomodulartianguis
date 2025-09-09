<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/perfil/perfilindex.css') }}">
    <title>Mi perfil</title>
</head>
<body>
<div class="page-container">
<main class="content">
@if (Auth::check() && Auth::user()->hasRole('administrador'))
    <br><x-barraadmin/>
@else
    <br><x-barrageneral/>
@endif
<div class="container mt-5">
    
    <div class="profile-card shadow">
    <div class="text-center mb-4">
        <h1 class="page-title"></i> Mi perfil</h1>
    </div><br>
    <div class="profile-avatar">
        @if (!empty($user->imagen))
            <img src="{{ Storage::disk('s3')->url($user->imagen) }}" alt="Foto de perfil" loading="lazy">
        @else
            <div class="no-img"><i class="fa-solid fa-user"></i></div>
        @endif
    </div>

    <div class="profile-body">
        <h3 class="profile-name">{{ $user->name }}</h3>
        <p class="profile-role">{{ $user->getRoleNames()->implode(', ') }}</p>

        <div class="profile-info-grid">
            <div>
                <h6><i class="fa-solid fa-at"></i> Usuario</h6>
                <span>{{ $user->nombre_usuario }}</span>
            </div>
            <div>
                <h6><i class="fa-solid fa-envelope"></i> Email</h6>
                <span>{{ $user->email }}</span>
            </div>
            <div>
                <h6><i class="fa-solid fa-venus-mars"></i> Género</h6>
                <span>
                    @switch($user->genero)
                        @case('H') Hombre @break
                        @case('M') Mujer @break
                        @case('O') Otro @break
                        @default No registrado
                    @endswitch
                </span>
            </div>
            <div>
                <h6><i class="fa-solid fa-cake-candles"></i> Edad</h6>
                <span>{{ $user->edad ?? 'No registrada' }} años</span>
            </div>
            <div>
                <h6><i class="fa-solid fa-phone"></i> Teléfono</h6>
                <span>{{ $user->telefono ?? 'No registrado' }}</span>
            </div>
            <div>
                <h6><i class="fa-solid fa-location-dot"></i> Dirección</h6>
                <span>{{ $user->direccion ?? 'No registrada' }}</span>
            </div>
            <div>
                <h6><i class="fa-solid fa-star"></i> Nivel usuario</h6>
                <span>{{ $user->nivel_usuario }}</span>
            </div>
        </div>


        <div class="text-center mt-4">
            <a href="{{ route('perfil.editPerfil') }}" class="btn-edit-profile">Editar perfil</a>
        </div>
    </div>
</div>

</div>

</div>

</div>

</div>

</div>

</div>

</div>

<!-- Bootstrap JS (opcional) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</main>
<x-footer/>
</div>
</body>
</html>
