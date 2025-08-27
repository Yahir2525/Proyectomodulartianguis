<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/dashboard/admin.css') }}">
    <title>Página de inicio</title>
</head>
<body>
<br><x-barra></x-barra><br><br>
    <main>
        <div class="inicio-container">
            <center><h1>BLANCOS DOÑA COLCHAS</h1>
            <p class="frase">¡La mejor calidad y crédito a tu alcance!</p></center>
            <div class="collage">
                <figure class="tile wide">
                    <a href="{{ url('/producto') }}" aria-label="Ver productos: Almohada">
                        <img src="{{ asset('img/almohada.jpeg') }}" alt="Almohada">
                    </a>
                </figure>
                <figure class="tile square">
                    <a href="{{ url('/producto') }}" aria-label="Ver productos: Bata">
                        <img src="{{ asset('img/bata.jpeg') }}" alt="Bata">
                    </a>
                </figure>
                <figure class="tile tall">
                    <a href="{{ url('/producto') }}" aria-label="Ver productos: Cobertor">
                        <img src="{{ asset('img/cobertor.jpeg') }}" alt="Cobertor">
                    </a>
                </figure>
                <figure class="tile square">
                    <a href="{{ url('/producto') }}" aria-label="Ver productos: Colcha">
                        <img src="{{ asset('img/colcha.jpeg') }}" alt="Colcha">
                    </a>
                </figure>
                <figure class="tile wide">
                    <a href="{{ url('/producto') }}" aria-label="Ver productos: Cobija">
                        <img src="{{ asset('img/cobija.jpeg') }}" alt="Cobija">
                    </a>
                </figure>
            </div>
        </div>
    </main>
    <footer>
        © {{ date('Y') }} Blancos Doña Colchas - Todos los derechos reservados
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>
