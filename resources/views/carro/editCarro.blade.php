<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/carro/editCarro.css') }}">
    <title>Editar Carro</title>
</head>
<body>
<div class="page-container">
<main class="content">
<x-barracreate/>
<a id="top"></a>
<section>
    <br><hr class="hr-grueso"><center><h1>Editar carro</h1></center><hr class="hr-grueso"><br>
    @php
        $pedidoCerrado = $carro->pedido && $carro->pedido->estado_pedido == 0;
        $descontinuado = !$productoActual->estado_producto;
    @endphp

    @if(session('error'))
        <p style="color: red;">{{ session('error') }}</p>
    @endif

    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    @if($pedidoCerrado)
        <p style="color:red;"><strong>Este pedido ya está cerrado. No puedes editar el carro.</strong></p>
    @else
    <form action="{{ route('carro.edit', ['id_carro' => $carro->id_carro, 'id_producto' => $productoActual->id_producto]) }}" method="GET" >
        <div class="buscar">
            <label for="buscar">Buscar producto:</label>
            <input list="productos" id="buscar" name="buscar"
                placeholder="Ej. mesa, cortina..." value="{{ request('buscar') }}">
            <datalist id="productos">
                @foreach ($nombresUnicos as $nombre)
                    <option value="{{ $nombre }}">{{ $nombre }}</option>
                @endforeach
            </datalist>
            <button type="submit" class="btn btn-agregar">Buscar</button>
        </div>
        <div class="filtros">
            <div>
                <label for="tipo">Tipo:</label>
                <select name="tipo" id="tipo" class="form-input">
                    <option value="">-- Todos --</option>
                    @foreach($tipos as $tipo)
                        <option value="{{ $tipo }}" {{ request('tipo') == $tipo ? 'selected' : '' }}>{{ $tipo }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="material">Material:</label>
                <select name="material" id="material" class="form-input">
                    <option value="">-- Todos --</option>
                    @foreach($materiales as $material)
                        <option value="{{ $material }}" {{ request('material') == $material ? 'selected' : '' }}>{{ $material }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="color">Color:</label>
                <select name="color" id="color" class="form-input">
                    <option value="">-- Todos --</option>
                    @foreach($colores as $color)
                        <option value="{{ $color }}" {{ request('color') == $color ? 'selected' : '' }}>{{ $color }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="tamanio">Tamaño:</label>
                <select name="tamanio" id="tamanio" class="form-input">
                    <option value="">-- Todos --</option>
                    @foreach($tamanios as $tamanio)
                        <option value="{{ $tamanio }}" {{ request('tamanio') == $tamanio ? 'selected' : '' }}>{{ $tamanio }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="precio_min">Precio mínimo:</label>
                <input type="number" name="precio_min" id="precio_min" value="{{ request('precio_min') }}" class="form-input">
            </div>
            <div>
                <label for="precio_max">Precio máximo:</label>
                <input type="number" name="precio_max" id="precio_max" value="{{ request('precio_max') }}" class="form-input">
            </div>
            <div>
                <button type="submit" class="btn btn-registrar">Filtrar</button>
                <a href="{{ route('carro.edit', ['id_carro' => $carro->id_carro, 'id_producto' => $productoActual->id_producto]) }}" class="btn btn-gray">Limpiar</a>
            </div>
        </div>
    </form>

        {{-- 📋 Tabla de productos --}}
        <form action="{{ route('carro.update', ['carro' => $carro->id_carro, 'id_producto' => $productoActual->id_producto]) }}" method="POST">
            @csrf
            @method('PUT')

            <h3>Selecciona el producto</h3>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Seleccionar</th>
                            <th>Imagen</th>
                            <th>Nombre</th>
                            <th>Material</th>
                            <th>Color</th>
                            <th>Tamaño</th>
                            <th>Precio</th>
                            <th>Disponibles</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($productos as $producto)
                            @php
                                $esActual = $producto->id_producto == $productoActual->id_producto;
                                $sinStock = $producto->piezas_disponibles == 0;
                                $desactivado = !$producto->estado_producto;
                                $deshabilitado = (!$esActual && ($sinStock || $desactivado));
                            @endphp
                            <tr class="{{ $sinStock || $desactivado ? 'sin-stock' : '' }}">
                                <td>
                                    <input type="radio"
                                        name="id_producto"
                                        value="{{ $producto->id_producto }}"
                                        {{ $esActual ? 'checked' : '' }}
                                        {{ $deshabilitado ? 'disabled' : '' }}>
                                </td>
                                <td>
                                    @if (!empty($producto->imagen)) 
                                        <img src="{{ Storage::disk('s3')->url($producto->imagen) }}" alt="Foto de producto" width="200">
                                    @else
                                        <span>Sin imagen</span>
                                    @endif
                                </td>
                                <td>{{ $producto->nombre }}</td>
                                <td>{{ $producto->material }}</td>
                                <td>{{ $producto->color }}</td>
                                <td>{{ $producto->tamanio }}</td>
                                <td>${{ number_format($producto->precio_unitario, 2) }}</td>
                                <td class="{{ $sinStock ? 'resaltado' : '' }}">{{ $producto->piezas_disponibles }}</td>
                                <td>{{ $producto->estado_producto ? 'Activo' : 'Descontinuado' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Links de paginación --}}
            <div class="mt-4 d-flex justify-content-center">
                {{ $productos->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>

            <center><br>
            <label for="cantidad">Cantidad:</label>
            <input type="number"
                name="cantidad"
                id="cantidad"
                min="1"
                max="{{ $descontinuado ? $cantidad : $productoActual->piezas_disponibles }}"
                value="{{ $cantidad }}"
                required
                class="cant-input form-input"
            >
            @if($descontinuado)
                <p style="color: red;">
                    Este producto está descontinuado. Solo puedes reducir la cantidad actual (máximo {{ $cantidad }}).
                </p>
            @endif

            <br><br>
            <label for="id_pedido">Selecciona un pedido existente o crea uno nuevo:</label>
            <select name="id_pedido" class="form-input">
                <option value="" disabled selected>-- Ninguno --</option>
                <option value="nuevo">-- Crear nuevo pedido --</option>
                @foreach($pedidosUsuario as $pedido)
                    <option value="{{ $pedido->id_pedido }}"
                        {{ $carro->id_pedido == $pedido->id_pedido ? 'selected' : '' }}
                        {{ $pedido->estado_pedido == 0 ? 'disabled' : '' }}>
                        Pedido #{{ $pedido->id_pedido }}{{ $pedido->estado_pedido == 0 ? ' (cerrado)' : '' }}
                    </option>
                @endforeach
            </select>
            <br><br>
            <button type="submit" class="btn btn-registrar">Actualizar Carro</button>
        </form>
    @endif

    <br><br>
    <a href="{{ route('carro.index') }}" class="btn btn-danger">Cancelar</a>
    <a href="#top" class="btn btn-agregar" aria-label="Ir arriba">Ir arriba</a></center>
</section>
</main>
<x-footer/>
</div>
</body>
</html>
