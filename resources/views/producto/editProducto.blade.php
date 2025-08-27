<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/producto/editProducto.css') }}">
    <title>Editar Producto</title>
</head>
<body>
<br>
    <br><hr class="hr-grueso"><center><h1>Editar producto</h1></center><hr class="hr-grueso"><br>

    @if ($errors->any())
        <div style="color: red;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    <form action="{{ route('producto.update', $producto->id_producto) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <label for="nombre">Nombre del producto:</label><br>
        <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $producto->nombre) }}"><br><br>

        <label class="label" for="imagen">Actualizar imagen:</label>
        <div class="control">
            <input class="input" type="file" name="imagen" id="imagen" accept="image/*">
        </div>
        @if (!empty($producto->imagen_url))
            <figure class="mt-3">
                <p class="label">Actual:</p>
                <img src="{{ $producto->imagen_url }}" alt="Imagen actual" style="max-width: 150px;">
            </figure>
        @endif
        @error('imagen')
            <p class="help is-danger">{{ $message }}</p>
        @enderror


        <label for="tipo">Tipo de producto:</label><br>
        <input list="tipos" name="tipo" id="tipo" value="{{ old('tipo') }}">
        <datalist id="tipos">
            @foreach($tiposExistentes as $tipo)
                <option value="{{ $tipo }}">{{ $tipo }}</option>
            @endforeach
        </datalist>
        <br><br>

        <label for="material">Material:</label><br>
        <input list="materiales" name="material" id="material" value="{{ old('material') }}">
        <datalist id="materiales">
            @foreach($materialesExistentes as $material)
                <option value="{{ $material }}">
            @endforeach
        </datalist>
        <br><br>

        <label for="color">Color:</label><br>
        <input list="colores" name="color" id="color" value="{{ old('color') }}">
        <datalist id="colores">
            @foreach($coloresExistentes as $color)
                <option value="{{ $color }}">
            @endforeach
        </datalist>
        <br><br>

        <label for="tamanio">Tamaño:</label><br>
        <input list="tamanios" name="tamanio" id="tamanio" value="{{ old('tamanio') }}">
        <datalist id="tamanios">
            @foreach($tamaniosExistentes as $tamanio)
                <option value="{{ $tamanio }}">
            @endforeach
        </datalist>
        <br><br>

        <label for="marca">Marca:</label><br>
        <input list="marcas" name="marca" id="marca" value="{{ old('marca') }}">
        <datalist id="marcas">
            @foreach($marcasExistentes as $marca)
                <option value="{{ $marca }}">
            @endforeach
        </datalist>
        <br><br>

        <label for="precio_unitario">Precio unitario ($):</label><br>
        <input type="number" name="precio_unitario" id="precio_unitario" step="0.01" min="0" value="{{ old('precio_unitario', $producto->precio_unitario) }}"><br><br>

        <label for="piezas">Piezas disponibles:</label><br>
        <input type="number" name="piezas" id="piezas" min="0" value="{{ old('piezas', $producto->piezas) }}"><br><br>
        
        <label>
            <input type="checkbox" name="estado_producto" value="1" {{ old('estado_producto', $producto->estado_producto ?? true) ? 'checked' : '' }}>
            Producto activo
        </label>

        <button type="submit">Actualizar producto</button>
        <a href="{{ route('producto.index') }}">Cancelar</a>
    </form>
</body>
</html>
