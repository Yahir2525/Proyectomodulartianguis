<!-- <!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="/img/hinataico.ico" />
    <link rel="stylesheet" href="{{ asset('css/compras/editCompras.css') }}">
    <link href="https://fonts.googleapis.com/css?family=Questrial&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/bulma@0.9.4/css/bulma.min.css" />
    <title>Editar Pedido</title>
</head>
<body>
<section class="hero is-success is-fullheight">
    <div class="hero-body">
        <div class="container has-text-centered">
            <div class="column is-4 is-offset-4">
                <h1 class="title">Editar Pedido</h1>
                <hr class="login-hr">
                <p class="subtitle has-text-white">Modifica los datos necesarios</p>

                <div class="box">
                    <form method="POST" action="{{ route('pedido.update', $pedido->id_pedido) }}">
                        @csrf
                        @method('PUT')

                        <label>Método de pago:</label>
                        <select name="metodo_pago">
                            <option value="contado" {{ $pedido->metodo_pago === 'contado' ? 'selected' : '' }}>Contado</option>
                            <option value="credito" {{ $pedido->metodo_pago === 'credito' ? 'selected' : '' }}>Crédito</option>
                        </select>

                        <br>

                        <label>Crédito (se usará solo si eliges "crédito"):</label>
                        <select name="id_credito">
                            <option value="">-- Selecciona un crédito --</option>
                            @foreach($creditos as $credito)
                                <option value="{{ $credito->id_credito }}" {{ $pedido->id_credito == $credito->id_credito ? 'selected' : '' }}>
                                    Crédito #{{ $credito->id_credito }} - ${{ $credito->saldo_total }}
                                </option>
                            @endforeach
                        </select>

                        <br>

                        <label>Estado del pedido:</label>
                        <input type="number" name="estado_pedido" value="{{ $pedido->estado_pedido ?? '' }}">

                        <br>
                        <button type="submit">Guardar cambios</button>
                    </form>
                    <br>
                    <a href="{{ url('/pedido') }}" class="button is-light is-fullwidth">Cancelar</a>
                </div>
            </div>
        </div>
    </div>
</section>
</body>
</html> -->
