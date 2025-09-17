<nav class="navbar navbar-expand-lg navbar-dark px-3">
    <a class="navbar-brand fw-semibold d-flex align-items-center" href="/">
        <img src="{{ asset('img/blanco.ico') }}" 
            alt="Logo Blancos Doña Colchas" 
            width="30" 
            class="rounded-circle me-2">
        Blancos Doña Colchas
    </a>    
    <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav mx-auto align-items-lg-center">
                <li class="nav-item"><a class="nav-link btn-chip" href="/">Inicio</a></li>
            </ul>
    </div>
</nav>

<style>
    nav { background: transparent !important; }

    .navbar-brand { 
    font-weight: 700; 
    color: #222; 
    }

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

    @media (max-width: 991.98px) {
    .btn-chip { width: 100%; justify-content: center; }
    }

    .navbar .dropdown-menu {
    min-width: auto;
    width: auto;
    max-width: 250px;
    font-size: 0.9rem;
    padding: 4px 0;
    }
    .navbar .dropdown-menu .dropdown-item {
    padding: 6px 10px;
    white-space: nowrap;
    }
    .navbar .dropdown-menu .dropdown-item:hover {
    background-color: #f2f7ff;
    }

    @media (max-width: 991.98px) {
    .navbar .dropdown-menu {
        width: 100%;
        max-width: none;
    }
    }

    .navbar-toggler {
        padding: 8px 14px;
        font-size: 1.25rem;
        line-height: 1;
        border: none;
        width: auto;
    }

</style>
