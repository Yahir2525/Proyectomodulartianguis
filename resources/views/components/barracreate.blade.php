<nav class="navbar navbar-expand-lg navbar-dark px-3">
    <a class="navbar-brand fw-semibold" href="/">Blancos Doña Colchas</a>

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
                <li class="nav-item"><a class="nav-link btn-chip" href="/">Inicio</a></li>
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
                        <i class="fas fa-user fa-fw text-white"></i>
                    @endif
                    @else
                    <i class="fas fa-user fa-fw text-white"></i>
                    @endauth
                </span>
                </a>

                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    @auth
                        <li><span class="dropdown-item-text">Hola, {{ Auth::user()->name }}</span></li>
                        <li><hr class="dropdown-divider" /></li>
                        <li>
                            <a class="dropdown-item" href="/perfil">Ver perfil</a>
                        </li>
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
    /* Fondo morado */
    nav { background: transparent !important; }

    .navbar-brand { 
    font-weight: 700; 
    color: #222; 
    }

    /* Botones tipo chip */
    :root {
    --btn-chip-bg: #fff;
    --btn-chip-text: #4b2a7b;
    --btn-chip-bg-hover: rgba(255,255,255,.92);
    --btn-chip-text-hover: #2b1650;
    --btn-chip-shadow: 0 4px 10px rgba(0,0,0,.12);
    }
    .btn-chip {
    display: inline-flex; align-items: center;
    padding: .45rem .9rem; margin: .125rem;
    border-radius: 999px;
    border: 1px solid rgba(255,255,255,.25);
    background: var(--btn-chip-bg);
    color: var(--btn-chip-text) !important;
    font-weight: 600;
    box-shadow: var(--btn-chip-shadow);
    transition: transform .15s ease, box-shadow .15s ease, background .15s ease, color .15s ease;
    text-decoration: none;
    }
    .btn-chip:hover {
    transform: translateY(-1px);
    background: var(--btn-chip-bg-hover);
    color: var(--btn-chip-text-hover) !important;
    box-shadow: 0 6px 14px rgba(0,0,0,.16);
    text-decoration: none;
    }

    /* Avatar chip */
    .avatar-chip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: transparent;
    border-radius: 999px;
    padding: .25rem .6rem;
    transition: background .15s ease, transform .15s ease;
    line-height: 1;
    }
    .avatar-chip:hover {
    background: rgba(255,255,255,.26);
    transform: translateY(-1px);
    }
    .user-icon {
    width: 40px; height: 40px; border-radius: 50%;
    object-fit: cover; border: 2px solid rgba(255,255,255,.6);
    }
    .avatar-chip .fa-user {
    font-size: 20px; width: 20px; text-align: center;
    }

    /* Centrado real del menú entre el logo y el avatar */
    @media (min-width: 992px) {
    nav.navbar { position: relative; }
    #mainNavbar .navbar-nav.mx-auto {
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        gap: .35rem;
    }
    #mainNavbar .navbar-nav.ms-auto { margin-left: auto !important; }
    }

    /* Responsive: chips a 100% en móvil */
    @media (max-width: 991.98px) {
    .btn-chip { width: 100%; justify-content: center; }
    }

    /* Dropdown más compacto */
    .navbar .dropdown-menu {
    min-width: auto;       /* que no se alargue de más */
    width: auto;           /* se ajusta al contenido */
    max-width: 250px;      /* ancho máximo */
    font-size: 0.9rem;
    padding: 4px 0;
    }
    .navbar .dropdown-menu .dropdown-item {
    padding: 6px 10px;
    white-space: nowrap;   /* evita saltos de línea */
    }
    .navbar .dropdown-menu .dropdown-item:hover {
    background-color: #f2f7ff;
    }

    /* En móviles: menú al 100% */
    @media (max-width: 991.98px) {
    .navbar .dropdown-menu {
        width: 100%;
        max-width: none;
    }
    }

</style>
