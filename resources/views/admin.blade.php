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
</head>
<body><br>
    <x-barra></x-barra>
    <br><br>
    {{-- Contenido principal --}}
    <main>
        <div class="inicio-container">
            <center><h1>BLANCOS DOÑA COLCHAS</h1>
            <p class="frase">¡La mejor calidad y crédito a tu alcance!</p></center>
            <div class="collage">
                <figure class="tile wide">
                    <img src="{{ asset('img/almohada.jpeg') }}" alt="Kanao">
                </figure>
                <figure class="tile square">
                    <img src="{{ asset('img/bata.jpeg') }}" alt="Kanao">
                </figure>
                <figure class="tile tall">
                    <img src="{{ asset('img/cobertor.jpeg') }}" alt="Kanao">
                </figure>
                <figure class="tile square">
                    <img src="{{ asset('img/colcha.jpeg') }}" alt="Kanao">
                </figure>
                <figure class="tile wide">
                    <img src="{{ asset('img/cobija.jpeg') }}" alt="Kanao">
                </figure>
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
