<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="Blancos Doña Colchas" />
    <title>Página de inicio</title>
    <link href="{{ asset('css/template.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/dashboard/admin.css') }}">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        body { background: linear-gradient(to right, #6a11cb, #2575fc); color: #fff; font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif; }
        nav { background: rgba(0,0,0,0.7) !important; }
        .inicio-container { text-align: center; padding: 80px 20px; max-width: 1100px; margin: 0 auto; }
        .inicio-container h1 { font-size: 3rem; font-weight: 800; margin-bottom: 10px; letter-spacing: .5px; }
        .inicio-container p.frase { font-size: 1.35rem; font-style: italic; opacity: .95; margin-bottom: 40px; }
        .accesos { display: flex; justify-content: center; flex-wrap: wrap; gap: 16px; }
        .accesos a, .accesos form button { background: #fff; color: #2575fc; font-weight: 700; padding: 12px 18px; border-radius: 12px; text-decoration: none; border: none; font-size: 1rem; transition: transform .2s, box-shadow .2s, background .2s; box-shadow: 0 8px 18px rgba(0,0,0,.15); }
        .accesos a:hover, .accesos form button:hover { background: #f5f7ff; transform: translateY(-2px); box-shadow: 0 10px 22px rgba(0,0,0,.18); }
        .user-icon { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid rgba(255,255,255,.6); }
        footer { background: rgba(255,255,255,0.08); padding: 18px 0; text-align: center; color: #fff; margin-top: 50px; }
        .navbar-brand { font-weight: 700; }
    </style>
</head>
<body>
    {{-- Navbar superior --}}
    <nav class="navbar navbar-expand navbar-dark px-3">
        <a class="navbar-brand" href="{{ url('/') }}">Blancos Doña Colchas</a>
        <ul class="navbar-nav ms-auto">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    @if (Auth::check())
                        @if (!empty(Auth::user()->imagen_url))
                            <img src="{{ Auth::user()->imagen_url }}" alt="Foto de perfil" class="user-icon">
                        @else
                            <i class="fas fa-user fa-fw"></i>
                        @endif
                    @else
                        <i class="fas fa-user fa-fw"></i>
                    @endif
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    @if (Auth::check())
                        <li><span class="dropdown-item-text">Hola, {{ Auth::user()->name }}</span></li>
                        <li><hr class="dropdown-divider" /></li>
                        <li>
                            <form method="POST" action="{{ url('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">Cerrar sesión</button>
                            </form>
                        </li>
                    @else
                        <li><a href="{{ url('/login') }}" class="dropdown-item">Iniciar sesión</a></li>
                    @endif
                </ul>
            </li>
        </ul>
    </nav>

    {{-- Contenido principal --}}
    <main>
        <div class="inicio-container">
            <h1>BLANCOS DOÑA COLCHAS</h1>
            <p class="frase">¡La mejor calidad y crédito a tu alcance!</p>

            <div class="accesos">
                <form method="POST" action="{{ route('dataset.actualizar') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        Actualizar dataset
                    </button>
                </form>
                <form method="GET" action="{{ route('predicciones.aplicar') }}">
                    <button type="submit" class="btn btn-primary">
                        Predicciones
                    </button>
                </form>
                <a href="{{ route('producto.index') }}">Productos</a>
                <a href="{{ route('carro.index') }}">Carros</a>
                <a href="{{ route('pedido.index') }}">Pedidos</a>
                <a href="{{ route('credito.index') }}">Créditos</a>
                <a href="{{ route('abono.index') }}">Abonos</a>
                <a href="{{ route('user.index') }}">Usuarios</a>
                <a href="{{ route('role.index') }}">Roles</a>
                <a href="{{ route('permission.index') }}">Permisos</a>
            </div>
        </div>
    </main>

    {{-- Footer --}}
    <footer>
        © {{ date('Y') }} Blancos Doña Colchas - Todos los derechos reservados
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>
