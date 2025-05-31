<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="/img/hinataico.ico" />
    <link rel="stylesheet" href="{{ asset('css/compras/editCompras.css') }}">
    <link href="https://fonts.googleapis.com/css?family=Questrial&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/bulma@0.9.4/css/bulma.min.css" />
    <link rel="stylesheet" type="text/css" href="{{ asset('css/compras/editCompras.css') }}">
    <title>Edit Pedidos</title>
</head>
<body>
<section class="hero is-success is-fullheight">
    <div class="hero-body">
        <div class="container has-text-centered">
            <div class="column is-4 is-offset-4">
                <h1 class="title">Editar Pedido</h1>
                <hr class="login-hr">
                <p class="subtitle has-text-white">Ingresa los nuevos datos</p>

                @php
                    $idPedido = request('id_pedido');
                    $totalPedido = request('total');
                @endphp

                <div class="box">
                    <form action="{{ url('/pedido', $pedido->id_pedido) }}" method="POST">
                    @csrf
                    @method('PUT')
                        <label>Total:</label>
                        <input type="text" name="total" value="{{ $totalPedido }}">
                        <div class="field">
                            <div class="control">
                                <button class="button is-block is-info is-large is-fullwidth" type="submit">Guardar cambios</button>
                            </div>
                        </div>
                    </form>
                    <!-- <form action="{{ route('pedido.destroy', $pedido->id_pedido) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <div class="field">
                            <div class="control">
                                <button class="button is-block is-danger is-large is-fullwidth" type="submit">Eliminar Compra</button>
                            </div>
                        </div>
                    </form> -->
                    <br>
                </div>
            </div>
        </div>
    </div>
</section>
</body>
</html>