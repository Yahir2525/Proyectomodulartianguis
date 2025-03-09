<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Principal de clientes</title>
</head>
<body>
    <section>
        <div>
            <h1>Principal de clientes</h1>
            <br>
            <a href="{{ url('/cliente/create') }}" class="button is-info is-fullwidth">
                Registrar una nueva compra
            </a><br><br>
            <form action="{{ url('/cliente/showCliente') }}" method="GET"> 
                <div class="sub">
                    <label for="id">ID de compra a buscar:</label>
                    <input type="text" id="id_cliente" name="id_cliente" placeholder="21" autofocus>
                </div><br><br>
                <input type="submit" id="enviar" name="enviar" value="buscar">
            </form>
            @if($clienteIndex->isNotEmpty())
                <br><h2>Tablas de clientes registrados</h2>
                @foreach ($clienteIndex as $cliente)
                    <center>
                    <table>
                    <tr>
                        <th colspan="2">Tabla del cliente: {{ $cliente->id_cliente }}</th>
                    </tr>
                    <tr>
                        <th>Atributo</th>
                        <th>Valor</th>
                    </tr>
                    <tr>
                        <td>ID</td>
                        <td>{{ $cliente->id_cliente }}</td>
                    </tr>
                    <tr>
                        <td>Nombre</td>
                        <td>{{ $cliente->nombre }}</td>
                    </tr>
                    <tr>
                        <td>Genero</td>
                        <td>{{ $cliente->genero }}</td>
                    </tr>
                    <tr>
                        <td>Edad</td>
                        <td>{{ $cliente->edad }}</td>
                    </tr>
                    <tr>
                        <td>Telefono</td>
                        <td>{{ $cliente->telefono }}</td>
                    </tr>
                    <tr>
                        <td>Direccion</td>
                        <td>{{ $cliente->direccion }}</td>
                    </tr>
                    <tr>
                        <td>Correo</td>
                        <td>{{ $cliente->correo}}</td>
                    </tr>
                    <tr>
                        <td>Nombre_usuario</td>
                        <td>{{ $cliente->nombre_usuario }}</td>
                    </tr>
                    <tr>
                        <td>Creacion</td>
                        <td>{{ $cliente->created_at }}</td>
                    </tr>
                    <tr>
                        <td>Actualización</td>
                        <td>{{ $cliente->updated_at }}</td>
                    </tr>
                </table>
                        <br>
                        <a href="{{ route('cliente.edit', $cliente->id_cliente) }}" class="button is-primary">Editar Cliente</a>
                        <form action="{{ route('cliente.destroy', $cliente->id_cliente) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <br><button type="submit" class="button is-danger">Eliminar Cliente</button>
                        </form>
                    </center>
                @endforeach 
            @endif
        </div>
    </section>
</body>
</html>