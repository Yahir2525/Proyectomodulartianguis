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
                <li class="nav-item"><a class="nav-link btn-chip" href="javascript:history.back()">Volver</a></li>
                <li class="nav-item"><a class="nav-link btn-chip" href="/">Página de inicio</a></li>
                <li class="nav-item"><a class="nav-link btn-chip" href="/producto">Productos</a></li>
                <li class="nav-item"><a class="nav-link btn-chip" href="/carro">Carros</a></li>
                <li class="nav-item"><a class="nav-link btn-chip" href="/pedido">Pedidos</a></li>
                <li class="nav-item"><a class="nav-link btn-chip" href="/credito">Créditos</a></li>
                <li class="nav-item"><a class="nav-link btn-chip" href="/abono">Abonos</a></li>

                @if ($isAdmin)
                    <li class="nav-item"><a class="nav-link btn-chip" href="/user">Usuarios</a></li>
                    <li class="nav-item"><a class="nav-link btn-chip" href="/role">Roles</a></li>
                    <li class="nav-item"><a class="nav-link btn-chip" href="/permission">Permisos</a></li>
                    <li class="nav-item">
                    <form method="POST" action="{{ route('dataset.actualizar') }}">
                        @csrf
                        <button type="submit" class="nav-link btn-chip">
                            Actualizar dataset
                        </button>
                    </form>
                    </li>
                    <li class="nav-item">
                    <form method="GET" action="{{ route('predicciones.aplicar') }}">
                        <button type="submit" class="nav-link btn-chip">
                            Predicciones
                        </button>
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
    nav { background: #c186ff !important; }

    .navbar-brand { font-weight: 700; }

    .user-icon {
        width: 40px; height: 40px; border-radius: 50%;
        object-fit: cover; border: 2px solid rgba(255,255,255,.6);
    }

    /* Centrar botones del menú */
    .navbar-nav.mx-auto {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: .25rem;
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
        color: var(--btn-chip-text) !important; font-weight: 600;
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

    /* Avatar como chip */
    /* La pastilla del avatar ahora es el span, no el <a> */
    .avatar-chip{
    display:inline-flex;              /* se encoge al contenido */
    align-items:center;
    justify-content:center;
    background:#c186ff;
    border-radius:999px;
    padding:.25rem .6rem;
    transition:background .15s ease, transform .15s ease;
    line-height:1;                    /* evita que “crezca” en alto */
    }

    .avatar-chip:hover{
    background:rgba(255,255,255,.26);
    transform:translateY(-1px);
    }

    /* Tamaños fijos del contenido */
    .user-icon{
    width:40px; height:40px; border-radius:50%;
    object-fit:cover; border:2px solid rgba(255,255,255,.6);
    }

    /* Si es ícono de FA (sin foto), fija el tamaño del glifo */
    .avatar-chip .fa-user{
    font-size:20px;                   /* ajusta a gusto (18–22px) */
    width:20px;                       /* asegura caja consistente */
    text-align:center;
    }


    /* Responsive */
    @media (max-width: 991.98px) {
        .btn-chip { width: 100%; justify-content: center; }
    }
</style>
