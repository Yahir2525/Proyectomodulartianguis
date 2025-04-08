<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="Inicio de sesión" />
        <meta name="author" content="Juan y Yahir" />
        <title>Login</title>
        <!-- <link href="{{asset('css/template.css')}}" rel="stylesheet" /> -->
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    </head>
    <body class="bg-primary">
    <div class="hero-body">
            <div class="container has-text-centered">
                <div class="column is-4 is-offset-4">
                    <h1>Inicio de sesión</h1>
                    <hr class="login-hr">
                    <p class="subtitle has-text-black">Por favor ingrese sus datos</p>
                    <div class="box">
                        <form>
                            <div class="field">
                                <div class="control">
                                    <input class="input is-large" type="email" placeholder="Correo:" autofocus="">
                                </div>
                            </div>
                            <div class="field">
                                <div class="control">
                                    <input class="input is-large" type="password" placeholder="Contraseña:">
                                </div>
                            </div>
                            <div class="field">
                                <!-- <label class="checkbox"> -->
                                <!-- <input type="checkbox"> Recordarme</label> -->
                            </div>
                            <a href="{{ url('/dashboard') }}" class="button is-block is-info is-large is-fullwidth">
                                Conectarse
                            </a>
                            <br><br>
                            <div>
                            <input class="button is-block is-primary is-large is-fullwidth" type="reset" value="Limpiar datos">
                            </div>
                        </form>
                        <br>
                        <br>
                            <div>
                            <a class="">
                                <a href="/">Inicio</a> <br>
                                <a href="{{ url('/registro') }}">Registrar</a>
                            </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
    </body>
</html>
