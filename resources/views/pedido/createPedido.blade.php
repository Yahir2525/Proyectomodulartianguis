<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Compra</title>
</head>
<body>
<section class="hero is-success is-fullheight">
        <div class="hero-body">
                    <h1 class="title">Registrar compras</h1>
                    <hr class="login-hr">

                    @php
                        $idPedido = request('id_pedido');
                        $totalPedido = request('total');
                    @endphp

                    <h1>Registrar Pedido #{{ $idPedido }}</h1>

                    <form action="{{ url('/pedido') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id_pedido" value="{{ $idPedido }}">
                        
                        <label>Total:</label>
                        <input type="text" name="total" value="{{ $totalPedido }}" readonly>

                        <!-- Otros campos -->

                        <button type="submit">Guardar pedido</button>
                    </form>

                        <!-- <p class="subtitle has-text-white">Ingresa los datos</p>
                            <div class="box">
                                <form action="{{ url('/pedido') }}" method="POST"> 
                                @csrf
                                    <div class="id_compra">
                                        <label for="id_compra">Compra:</label>
                                        <input type="id" id="id_compra" name="id_compra" placeholder="" value="{{ old('id_compra') }}" required>
                                    </div><br>
                                    <div class="id_producto">
                                        <label for="id_producto">Producto:</label>
                                        <input type="id" id="id_producto" name="id_producto" placeholder="" value="{{ old('id_producto') }}" required>
                                    </div><br>
                                    <div class="cantidad">
                                        <label for="cantidad">Cantidad:</label>
                                        <input type="number" id="cantidad" name="cantidad" placeholder="" value="{{ old('cantidad') }}" required>
                                    </div><br>
                                    <div class="precio_unitario">
                                        <label for="precio_unitario">Precio unitario:</label>
                                        <input type="number" id="precio_unitario" name="precio_unitario" placeholder="" value="{{ old('precio_unitario') }}" required>
                                    </div><br>
                                    <div class="subtotal">
                                        <label for="subtotal">subtotal:</label>
                                        <input type="number" id="subtotal" name="subtotal" placeholder="" value="{{ old('subtotal') }}" required>
                                    </div><br>
                                    <div class="total_pagar">
                                        <label for="total_pagar">total_pagar:</label>
                                        <input type="number" id="total_pagar" name="total_pagar" placeholder="" value="{{ old('total_pagar') }}" required>
                                    </div><br>
                                    
                                    <div class="control">
                                                <button class="button is-block is-info is-large is-fullwidth" type="submit">
                                                Enviar</button>
                                            </div> -->
                                </form><br>
                                    <p class="has-text-purple">
                                    <a href="/">Inicio</a>;
                                    <a href="/compras">Regresar</a>
                                    </p>

                            </div>

        </div>
</section>
</body>
</html>