<nav class="navbar navbar-expand-lg navbar-dark px-3">
    <a class="navbar-brand fw-semibold d-flex align-items-center" href="/">
        <img src="{{ asset('img/blanco.ico') }}" 
            alt="Logo Blancos Doña Colchas" 
            width="30" 
            class="rounded-circle me-2">
        Blancos Doña Colchas
    </a>
    

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar"
        aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    @php
        $isAuth  = Auth::check();
        $isAdmin = $isAuth && Auth::user()->hasRole('administrador');
        $avatarPath = $isAuth && !empty(Auth::user()->imagen)
            ? Storage::disk('s3')->url(Auth::user()->imagen)
            : null;
    @endphp

    <div class="collapse navbar-collapse" id="mainNavbar">
        @auth
        <ul class="navbar-nav mx-auto align-items-lg-center">
            @if ($isAdmin)
            <li class="nav-item"><a class="nav-link btn-chip" href="/user">Usuarios</a></li>
            <li class="nav-item"><a class="nav-link btn-chip" href="/role">Roles</a></li>
            <li class="nav-item"><a class="nav-link btn-chip" href="/permission">Permisos</a></li>

            <li class="nav-item">
                <form method="POST" action="{{ route('dataset.actualizar') }}">
                @csrf
                <button type="submit" class="nav-link btn-chip">Actualizar dataset</button>
                </form>
            </li>

            <li class="nav-item">
                <form method="GET" action="{{ route('predicciones.aplicar') }}">
                <button type="submit" class="nav-link btn-chip">Predicciones</button>
                </form>
            </li>
            @endif
        </ul>
        @endauth

        <ul class="navbar-nav ms-auto">
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" id="navbarDropdown" href="#"
            role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="avatar-chip">
                @auth
                @if ($avatarPath)
                    <img src="{{ $avatarPath }}" alt="Foto de perfil" class="user-icon">
                @else
                    <i class="fas fa-user fa-fw text-black"></i>
                @endif
                @else
                <i class="fas fa-user fa-fw text-black"></i>
                @endauth
            </span>
            </a>

            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
            @auth
                <li><span class="dropdown-item-text">Hola, {{ Auth::user()->name }}</span></li>
                <li><hr class="dropdown-divider" /></li>
                <li><a class="dropdown-item" href="/perfil">Ver perfil</a></li>
                <li>
                <form method="POST" action="{{ url('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item">Cerrar sesión</button>
                </form>
                </li>
            @else
                <li><a href="{{ url('/login') }}" class="dropdown-item">Iniciar sesión</a></li>
            @endauth
            </ul>
        </li>
        </ul>
    </div>
</nav>

<style>
nav { background: transparent !important; }
.navbar-brand { font-weight: 700; color: #222; }

:root{
    --btn-chip-bg:#fff; --btn-chip-text:#4b2a7b;
    --btn-chip-bg-hover:rgba(255,255,255,.92); --btn-chip-text-hover:#2b1650;
    --btn-chip-shadow:0 4px 10px rgba(0,0,0,.12);
}
.btn-chip{
    display:inline-flex; align-items:center;
    padding:.45rem .9rem; margin:.125rem; border-radius:999px;
    border:1px solid rgba(255,255,255,.25);
    background:var(--btn-chip-bg); color:var(--btn-chip-text) !important;
    font-weight:600; box-shadow:var(--btn-chip-shadow);
    transition:transform .15s ease, box-shadow .15s ease, background .15s ease, color .15s ease;
    text-decoration:none;
}
.btn-chip:hover{
    transform:translateY(-1px);
    background:var(--btn-chip-bg-hover); color:var(--btn-chip-text-hover) !important;
    box-shadow:0 6px 14px rgba(0,0,0,.16);
    text-decoration:none;
}

button.btn-chip{ -webkit-appearance:none; appearance:none; }

.navbar-nav .nav-item form{ display:inline; margin:0; }

.avatar-chip{
    display:inline-flex; align-items:center; justify-content:center;
    background:transparent; border-radius:999px; padding:.25rem .6rem;
    transition:background .15s ease, transform .15s ease; line-height:1;
}
.avatar-chip:hover{ background:rgba(255,255,255,.26); transform:translateY(-1px); }
.user-icon{ width:40px; height:40px; border-radius:50%; object-fit:cover; border:2px solid rgba(255,255,255,.6); }
.avatar-chip .fa-user{ font-size:20px; width:20px; text-align:center; }

@media (min-width: 992px){
    nav.navbar{ position:relative; }
    #mainNavbar .navbar-nav.mx-auto{
    position:absolute; left:50%; transform:translateX(-50%); gap:.35rem;
    }
    #mainNavbar .navbar-nav.ms-auto{ margin-left:auto !important; }
}

@media (max-width: 991.98px){
    .btn-chip{ width:100%; justify-content:center; }
}

.navbar .dropdown-menu{
    min-width:auto; width:auto; max-width:250px;
    font-size:.9rem; padding:4px 0;
}

.navbar .dropdown-menu .dropdown-item{
    display:block; width:100%;
    padding:6px 10px; margin:0; text-align:left;
    font:inherit;
    color:#222 !important;
    background:transparent !important;
    border:0 !important; border-radius:0; box-shadow:none !important;
    line-height:1.25; white-space:nowrap;
}
.navbar .dropdown-menu .dropdown-item:hover,
.navbar .dropdown-menu .dropdown-item:focus,
.navbar .dropdown-menu .dropdown-item:active{
    background:#f2f7ff !important; color:#222 !important; outline:0;
}

.navbar .dropdown-menu form{ display:block; margin:0; padding:0; }
.navbar .dropdown-menu button.dropdown-item{ -webkit-appearance:none; appearance:none; }

@media (max-width: 991.98px){
    .navbar .dropdown-menu{ width:100%; max-width:none; }
}

.navbar .dropdown-toggle::after {
    border-top-color: #111 !important;
}

.navbar-toggler {
    padding: 8px 14px;
    font-size: 1.25rem;
    line-height: 1;
    border: none;
    width: auto;
}
</style>
