<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="{{ asset('css/producto/createProducto.css') }}">
    <title>Registrar Producto</title>
</head>
<body>
    <h1>Registrar nuevo producto</h1>
    
    @if ($errors->any())
        <div style="color: red;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('producto.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('POST')
        <label for="nombre">Nombre del producto:</label><br>
        <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" required><br><br>

        <label for="imagen">Imagen del producto:</label><br>
        <input type="file" name="imagen" id="imagen" accept="image/*">
        @if (isset($producto) && !empty($producto->imagen_url))
            <div class="mt-2">
                <img src="{{ $producto->imagen_url }}" alt="Imagen seleccionada"
                    class="img-thumbnail img-fluid" style="max-width:150px" loading="lazy">
            </div>
        @endif<br><br>             
        <label for="tipo">Tipo de producto:</label><br>
        <input list="tipos" name="tipo" id="tipo" value="{{ old('tipo') }}" required>
        <datalist id="tipos">
            @foreach($tiposExistentes as $tipo)
                <option value="{{ $tipo }}">
            @endforeach
        </datalist><br><br>
        <label for="material">Material:</label><br>
        <input list="materiales" name="material" id="material" value="{{ old('material') }}" required>
        <datalist id="materiales">
            @foreach($materialesExistentes as $material)
                <option value="{{ $material }}">
            @endforeach
        </datalist>
        <br><br>

        <label for="color">Color:</label><br>
        <input list="colores" name="color" id="color" value="{{ old('color') }}" required>
        <datalist id="colores">
            @foreach($coloresExistentes as $color)
                <option value="{{ $color }}">
            @endforeach
        </datalist>
        <br><br>

        <label for="tamanio">Tamaño:</label><br>
        <input list="tamanios" name="tamanio" id="tamanio" value="{{ old('tamanio') }}" required>
        <datalist id="tamanios">
            @foreach($tamaniosExistentes as $tamanio)
                <option value="{{ $tamanio }}">
            @endforeach
        </datalist>
        <br><br>

        <label for="marca">Marca:</label><br>
        <input list="marcas" name="marca" id="marca" value="{{ old('marca') }}" required>
        <datalist id="marcas">
            @foreach($marcasExistentes as $marca)
                <option value="{{ $marca }}">
            @endforeach
        </datalist>
        <br><br>

        <label for="precio_unitario">Precio unitario ($):</label><br>
        <input type="number" name="precio_unitario" id="precio_unitario" step="0.01" min="0" value="{{ old('precio_unitario') }}" required><br><br>

        <label for="piezas">Piezas disponibles:</label><br>
        <input type="number" name="piezas" id="piezas" min="0" value="{{ old('piezas') }}" required><br><br>

        <label>
            <input type="checkbox" name="estado_producto" value="1" {{ old('estado_producto', $producto->estado_producto ?? true) ? 'checked' : '' }}>
            Producto activo
        </label>

        <button type="submit">Registrar producto</button>
        <a href="{{ url('/producto') }}">Cancelar</a>
    </form>
</body>
</html>
