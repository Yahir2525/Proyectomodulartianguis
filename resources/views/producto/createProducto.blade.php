<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/producto/createProducto.css') }}">
    <link rel="icon" href="{{ asset('img/blanco.ico') }}" type="image/x-icon">
    <title>Registrar producto</title>
</head>
<body>
    <div class="page-container">
        <main class="content">
        <br><x-barracreate/>
            <section class="container">
                <hr class="hr-grueso"><h1 class="text-center">Registrar nuevo producto</h1><hr class="hr-grueso">

                @if ($errors->any())
                    <div class="errors">
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

                    <label for="nombre">Nombre del producto:</label>
                    <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" required>

                    <label for="imagen">Imagen del producto:</label>
                    <input type="file" name="imagen" id="imagen" accept="image/*">
                    @if (isset($producto) && !empty($producto->imagen_url))
                        <div class="mt-2 text-center">
                            <img src="{{ $producto->imagen_url }}" alt="Imagen seleccionada"
                                class="img-thumbnail img-fluid" style="max-width:150px" loading="lazy">
                        </div>
                    @endif

                    <label for="tipo">Tipo de producto:</label>
                    <input list="tipos" name="tipo" id="tipo" value="{{ old('tipo') }}" required>
                    <datalist id="tipos">
                        @foreach($tiposExistentes as $tipo)
                            <option value="{{ $tipo }}">
                        @endforeach
                    </datalist>

                    <label for="material">Material:</label>
                    <input list="materiales" name="material" id="material" value="{{ old('material') }}" required>
                    <datalist id="materiales">
                        @foreach($materialesExistentes as $material)
                            <option value="{{ $material }}">
                        @endforeach
                    </datalist>

                    <label for="color">Color:</label>
                    <input list="colores" name="color" id="color" value="{{ old('color') }}" required>
                    <datalist id="colores">
                        @foreach($coloresExistentes as $color)
                            <option value="{{ $color }}">
                        @endforeach
                    </datalist>

                    <label for="tamanio">Tamaño:</label>
                    <input list="tamanios" name="tamanio" id="tamanio" value="{{ old('tamanio') }}" required>
                    <datalist id="tamanios">
                        @foreach($tamaniosExistentes as $tamanio)
                            <option value="{{ $tamanio }}">
                        @endforeach
                    </datalist>

                    <label for="marca">Marca:</label>
                    <input list="marcas" name="marca" id="marca" value="{{ old('marca') }}" required>
                    <datalist id="marcas">
                        @foreach($marcasExistentes as $marca)
                            <option value="{{ $marca }}">
                        @endforeach
                    </datalist>

                    <label for="precio_unitario">Precio unitario ($):</label>
                    <input type="number" name="precio_unitario" id="precio_unitario" step="0.01" min="0" value="{{ old('precio_unitario') }}" required>

                    <label for="piezas">Piezas disponibles:</label>
                    <input type="number" name="piezas" id="piezas" min="0" value="{{ old('piezas') }}" required>

                    <label class="switch">
                        <input type="checkbox" name="estado_producto" value="1"
                            {{ old('estado_producto', $producto->estado_producto ?? true) ? 'checked' : '' }}>
                        Producto activo
                    </label>

                    <button type="submit" class="btn btn-primary">Registrar producto</button>

                    <div class="back-wrap text-center">
                        <a href="{{ url('/producto') }}" class="btn btn-danger">Cancelar</a>
                    </div>
                </form>
            </section>
        </main>
        <x-footer/>
    </div>
</body>
</html>
