<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/producto/editProducto.css') }}">
    <link rel="icon" href="{{ asset('img/blanco.ico') }}" type="image/x-icon">
    <title>Editar producto</title>
</head>
<body>
    <div class="page-container">
        <main class="content">
        <br><x-barracreate/>
            <div class="container">
                <br><hr class="hr-grueso"><h1>Editar producto</h1><hr class="hr-grueso"><br>

                @if ($errors->any())
                    <div class="errors" role="alert">
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

                    <label for="nombre">Nombre del producto:</label>
                    <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $producto->nombre) }}">

                    <label for="imagen">Actualizar imagen:</label>
                    <input type="file" name="imagen" id="imagen" accept="image/*">
                    @if (!empty($producto->imagen_url))
                        <div class="mt-2">
                            <p><strong>Imagen actual:</strong></p>
                            <img src="{{ $producto->imagen_url }}" alt="Imagen actual" style="max-width:150px; border-radius:6px;">
                        </div>
                    @endif

                    <label for="tipo">Tipo de producto:</label>
                    <input list="tipos" name="tipo" id="tipo" value="{{ old('tipo', $producto->tipo) }}">
                    <datalist id="tipos">
                        @foreach($tiposExistentes as $tipo)
                            <option value="{{ $tipo }}">
                        @endforeach
                    </datalist>

                    <label for="material">Material:</label>
                    <input list="materiales" name="material" id="material" value="{{ old('material', $producto->material) }}">
                    <datalist id="materiales">
                        @foreach($materialesExistentes as $material)
                            <option value="{{ $material }}">
                        @endforeach
                    </datalist>

                    <label for="color">Color:</label>
                    <input list="colores" name="color" id="color" value="{{ old('color', $producto->color) }}">
                    <datalist id="colores">
                        @foreach($coloresExistentes as $color)
                            <option value="{{ $color }}">
                        @endforeach
                    </datalist>

                    <label for="tamanio">Tamaño:</label>
                    <input list="tamanios" name="tamanio" id="tamanio" value="{{ old('tamanio', $producto->tamanio) }}">
                    <datalist id="tamanios">
                        @foreach($tamaniosExistentes as $tamanio)
                            <option value="{{ $tamanio }}">
                        @endforeach
                    </datalist>

                    <label for="marca">Marca:</label>
                    <input list="marcas" name="marca" id="marca" value="{{ old('marca', $producto->marca) }}">
                    <datalist id="marcas">
                        @foreach($marcasExistentes as $marca)
                            <option value="{{ $marca }}">
                        @endforeach
                    </datalist>

                    <label for="precio_unitario">Precio unitario ($):</label>
                    <input type="number" name="precio_unitario" id="precio_unitario" step="0.01" min="0" value="{{ old('precio_unitario', $producto->precio_unitario) }}">

                    <label for="piezas">Piezas disponibles:</label>
                    <input type="number" name="piezas" id="piezas" min="0" value="{{ old('piezas', $producto->piezas) }}">

                    <label class="switch">
                        <input type="checkbox" name="estado_producto" value="1" 
                            {{ old('estado_producto', $producto->estado_producto ?? true) ? 'checked' : '' }}>
                        Producto activo
                    </label>

                    <button type="submit" class="btn btn-primary">Actualizar producto</button>

                    <center><div class="back-wrap">
                        <a href="{{ route('producto.index') }}" class="btn btn-danger">Cancelar</a>
                    </div></center>
                </form>
            </div>
        </main>
        <x-footer/>
    </div>
</body>
</html>
