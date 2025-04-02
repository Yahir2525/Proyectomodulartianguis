<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Principal de pedidos</title>
</head>
<body>
    <section>
        <div>
            <h1>Principal de pedidos</h1>
            <br>
            <!-- <form action="{{ url('/pedido/showPedido') }}" method="GET"> 
                <div class="sub">
                    <label for="id">ID de compra a buscar:</label>
                    <input type="text" id="id" name="id_pedido" placeholder="21" autofocus>
                </div><br><br>
                <input type="submit" id="enviar" name="enviar" value="buscar">
            </form> -->
            @if($carroIndex->isNotEmpty())                
                    <center>
                    @foreach ($carroIndex as $carrito)
                        <table border="1">
                        @foreach($carrito->productos as $producto)
                        {{$producto->nombre}}
                        {{$producto->pivot->cantidad}}
                        {{$producto->precio_unitario}}
                        {{$producto->pivot->cantidad * $producto->precio_unitario}}
                        
                        @endforeach
                        @endforeach
                        @php 
                        $totalCarrito = $carroIndex->flatMap->productos->sum
                        (function($producto)
                        {
                            return $producto->pivot->cantidad * $producto->precio_unitario;
                        });
                        @endphp
                        total: {{$totalCarrito}}
                    </table>
                    </center>
            @endif
        </div>
    </section>
</body>
</html>
