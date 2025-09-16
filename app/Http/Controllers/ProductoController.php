<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Pedido;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $usuario = Auth::user();

        $seleccion = $request->input('sel', []);

        if ($usuario && $usuario->hasRole('administrador')) {
            $query = Producto::query();
        } else {
            $query = Producto::where('estado_producto', true);
        }

        if ($request->filled('buscar')) {
            $busqueda = $request->input('buscar');

            if (is_numeric($busqueda)) {
                $query->where('id_producto', 'LIKE', "%{$busqueda}%");
            } else {
                $query->where(function ($q) use ($busqueda) {
                    $q->where('nombre', 'ILIKE', "%{$busqueda}%")
                    ->orWhere('material', 'ILIKE', "%{$busqueda}%")
                    ->orWhere('color', 'ILIKE', "%{$busqueda}%")
                    ->orWhere('tamanio', 'ILIKE', "%{$busqueda}%");
                });
            }
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        if ($request->filled('material')) {
            $query->where('material', $request->material);
        }
        if ($request->filled('color')) {
            $query->where('color', $request->color);
        }
        if ($request->filled('tamanio')) {
            $query->where('tamanio', $request->tamanio);
        }
        if ($request->filled('precio_min')) {
            $query->where('precio_unitario', '>=', $request->precio_min);
        }
        if ($request->filled('precio_max')) {
            $query->where('precio_unitario', '<=', $request->precio_max);
        }
        if ($request->filled('estado')) {
            $query->where('estado_producto', $request->estado);
        }

        $productoIndex = $query
            ->orderBy('tipo')
            ->orderBy('id_producto')
            ->paginate(10)
            ->withQueryString();

        $tipos      = Producto::select('tipo')->distinct()->pluck('tipo');
        $materiales = Producto::select('material')->distinct()->pluck('material');
        $colores    = Producto::select('color')->distinct()->pluck('color');
        $tamanios   = Producto::select('tamanio')->distinct()->pluck('tamanio');

        $nombresUnicos = Producto::select('nombre')
            ->distinct()
            ->orderBy('nombre')
            ->limit(7)
            ->pluck('nombre');

        $usuarios = collect();
        $pedidosUsuario = collect();

        if ($usuario) {
            if ($usuario->hasRole('administrador')) {
                $usuarios = User::all();
                $pedidosUsuario = Pedido::with('user')
                    ->where('estado_pedido', 1)
                    ->get();
            } else {
                $pedidosUsuario = Pedido::where('id_user', $usuario->id_user)
                    ->where('estado_pedido', 1)
                    ->get();
            }
        }

        return view('producto.productoIndex', compact(
            'productoIndex',
            'pedidosUsuario',
            'usuarios',
            'materiales',
            'colores',
            'tamanios',
            'tipos',
            'nombresUnicos',
            'seleccion',
        ));
    }

    public function create()
    {
        $tiposExistentes = Producto::select('tipo')->distinct()->pluck('tipo');
        $materialesExistentes = Producto::select('material')->distinct()->pluck('material');
        $coloresExistentes = Producto::select('color')->distinct()->pluck('color');
        $tamaniosExistentes = Producto::select('tamanio')->distinct()->pluck('tamanio');
        $marcasExistentes = Producto::select('marca')->distinct()->pluck('marca');

        return view('producto.createProducto', compact(
            'tiposExistentes',
            'materialesExistentes',
            'coloresExistentes',
            'tamaniosExistentes',
            'marcasExistentes'
        ));
    }

    public function store(Request $request)
    {
        $producto = new Producto();

        $request->validate([
            'imagen'           => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'nombre'           => ['required', 'string', 'max:150'],
            'tipo'             => ['nullable', 'string', 'max:100'],
            'material'         => ['nullable', 'string', 'max:100'],
            'color'            => ['nullable', 'string', 'max:50'],
            'tamanio'          => ['nullable', 'string', 'max:50'],
            'marca'            => ['nullable', 'string', 'max:100'],
            'precio_unitario'  => ['required', 'numeric', 'min:0'],
            'piezas'           => ['required', 'integer', 'min:0'],
            'estado_producto'  => ['nullable', 'boolean'],
        ], [
            'imagen.image'   => 'El archivo debe ser una imagen.',
            'imagen.mimes'   => 'La imagen debe estar en formato JPG, JPEG, PNG o WEBP.',
            'imagen.max'     => 'La imagen no puede superar los 5 MB.',

            'nombre.required' => 'El nombre del producto es obligatorio.',
            'nombre.string'   => 'El nombre debe ser texto válido.',
            'nombre.max'      => 'El nombre no puede tener más de 150 caracteres.',

            'tipo.string' => 'El tipo debe ser texto válido.',
            'tipo.max'    => 'El tipo no puede tener más de 100 caracteres.',

            'material.string' => 'El material debe ser texto válido.',
            'material.max'    => 'El material no puede tener más de 100 caracteres.',

            'color.string' => 'El color debe ser texto válido.',
            'color.max'    => 'El color no puede tener más de 50 caracteres.',

            'tamanio.string' => 'El tamaño debe ser texto válido.',
            'tamanio.max'    => 'El tamaño no puede tener más de 50 caracteres.',

            'marca.string' => 'La marca debe ser texto válido.',
            'marca.max'    => 'La marca no puede tener más de 100 caracteres.',

            'precio_unitario.required' => 'El precio unitario es obligatorio.',
            'precio_unitario.numeric'  => 'El precio unitario debe ser un número.',
            'precio_unitario.min'      => 'El precio unitario no puede ser negativo.',

            'piezas.required' => 'La cantidad de piezas es obligatoria.',
            'piezas.integer'  => 'Las piezas deben ser un número entero.',
            'piezas.min'      => 'Las piezas no pueden ser negativas.',

            'estado_producto.boolean' => 'El estado del producto debe ser válido (activo o inactivo).',
        ]);

        if ($request->hasFile('imagen')) {
            $file        = $request->file('imagen');
            $filename    = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
            $relative    = 'img/' . $filename;

            if (!empty($user->imagen)) {
                if (config('filesystems.default') === 's3') {
                    try { Storage::disk('s3')->delete($producto->imagen); } catch (\Throwable $e) {}
                } else {
                    $old = public_path($producto->imagen);
                    if (is_file($old)) @unlink($old);
                }
            }

            if (config('filesystems.default') === 's3') {
                Storage::disk('s3')->putFileAs('img', $file, $filename, [
                    'visibility'  => 'private',
                    'ContentType' => $file->getMimeType(),
                ]);
            } else {
                $file->move(public_path('img'), $filename);
            }

            $producto->imagen = $relative;
        }

        $producto->nombre = $request->input('nombre');
        $producto->tipo = $request->input('tipo');
        $producto->material = $request->input('material');
        $producto->color = $request->input('color');
        $producto->tamanio = $request->input('tamanio');
        $producto->marca = $request->input('marca');
        $producto->precio_unitario = $request->input('precio_unitario');
        $producto->piezas = $request->input('piezas');
        $producto->estado_producto = $request->input('estado_producto', true);
        $producto->save();

        return redirect('/producto')->with('success', 'Producto registrado correctamente.');
    }

    public function show(Request $request)
    {
        $busqueda = $request->input('buscar');

        $seleccion = $request->input('sel', []);

        if (!$busqueda) {
            return redirect()->route('producto.index')
            ->with('info', 'Se mostró la lista completa porque no ingresaste ningún criterio de búsqueda.');
        }

        if (is_numeric($busqueda)) {
            $producto = Producto::find($busqueda);

            if ($producto) {
                $productos = new LengthAwarePaginator(
                    [$producto],
                    1,
                    10,
                    $request->input('page', 1),
                    ['path' => $request->url(), 'query' => $request->query()]
                );
            } else {
                $productos = collect();
            }
        } else {
            $productos = Producto::where('nombre', 'ILIKE', "%$busqueda%")
                ->orderBy('tipo')
                ->orderBy('id_producto')
                ->paginate(10)
                ->withQueryString();
        }

        $nombresUnicos = Producto::select('nombre')
        ->distinct()
        ->orderBy('nombre')
        ->limit(7)
        ->pluck('nombre');

        $usuarios = User::all();
        $pedidosUsuario = Pedido::with('user')->get();

        return view('producto.showProducto', compact('productos', 'usuarios', 'pedidosUsuario', 'nombresUnicos', 'seleccion'));
    }

    public function edit($id)
    {
        $producto = Producto::find($id);
        if (!$producto) {
            return back()->with('error', 'Producto no encontrado.');
        }

        $tiposExistentes = Producto::select('tipo')->distinct()->pluck('tipo');
        $materialesExistentes = Producto::select('material')->distinct()->pluck('material');
        $coloresExistentes = Producto::select('color')->distinct()->pluck('color');
        $tamaniosExistentes = Producto::select('tamanio')->distinct()->pluck('tamanio');
        $marcasExistentes = Producto::select('marca')->distinct()->pluck('marca');

        return view('producto.editProducto', compact(
            'producto',
            'tiposExistentes',
            'materialesExistentes',
            'coloresExistentes',
            'tamaniosExistentes',
            'marcasExistentes'
        ));
    }

    public function update(Request $request, $id)
    {
        $producto = Producto::find($id);
        if (!$producto) {
            return redirect()->route('producto.index')->with('error', 'Producto no encontrado.');
        }

        $request->validate([
            'imagen'           => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'nombre'           => ['nullable', 'string', 'max:150'],
            'tipo'             => ['nullable', 'string', 'max:100'],
            'material'         => ['nullable', 'string', 'max:100'],
            'color'            => ['nullable', 'string', 'max:50'],
            'tamanio'          => ['nullable', 'string', 'max:50'],
            'marca'            => ['nullable', 'string', 'max:100'],
            'precio_unitario'  => ['nullable', 'numeric', 'min:0'],
            'piezas'           => ['nullable', 'integer', 'min:0'],
            'estado_producto'  => ['nullable', 'boolean'],
        ], [
            'imagen.image'   => 'El archivo debe ser una imagen.',
            'imagen.mimes'   => 'La imagen debe estar en formato JPG, JPEG, PNG o WEBP.',
            'imagen.max'     => 'La imagen no puede superar los 5 MB.',

            'nombre.string'  => 'El nombre debe ser texto válido.',
            'nombre.max'     => 'El nombre no puede tener más de 150 caracteres.',

            'tipo.string'    => 'El tipo debe ser texto válido.',
            'tipo.max'       => 'El tipo no puede tener más de 100 caracteres.',

            'material.string'=> 'El material debe ser texto válido.',
            'material.max'   => 'El material no puede tener más de 100 caracteres.',

            'color.string'   => 'El color debe ser texto válido.',
            'color.max'      => 'El color no puede tener más de 50 caracteres.',

            'tamanio.string' => 'El tamaño debe ser texto válido.',
            'tamanio.max'    => 'El tamaño no puede tener más de 50 caracteres.',

            'marca.string'   => 'La marca debe ser texto válido.',
            'marca.max'      => 'La marca no puede tener más de 100 caracteres.',

            'precio_unitario.numeric' => 'El precio unitario debe ser un número.',
            'precio_unitario.min'     => 'El precio unitario no puede ser negativo.',

            'piezas.integer' => 'Las piezas deben ser un número entero.',
            'piezas.min'     => 'Las piezas no pueden ser negativas.',

            'estado_producto.boolean' => 'El estado del producto debe ser válido (activo o inactivo).',
        ]);

        $urlTemporal = null;

        if ($request->hasFile('imagen')) {
            $archivo       = $request->file('imagen');
            $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
            $rutaRelativa  = 'img/' . $nombreArchivo;

            if ($producto->imagen) {
                if (config('filesystems.default') === 's3') {
                    try { Storage::disk('s3')->delete($producto->imagen); } catch (\Throwable $e) {}
                } else {
                    $rutaFisica = public_path($producto->imagen);
                    if (is_file($rutaFisica)) @unlink($rutaFisica);
                }
            }

            if (config('filesystems.default') === 's3') {
                Storage::disk('s3')->putFileAs('img', $archivo, $nombreArchivo, [
                    'visibility'  => 'private',
                    'ContentType' => $archivo->getMimeType(),
                ]);

                $urlTemporal = Storage::disk('s3')->temporaryUrl($rutaRelativa, now()->addMinutes(10));
            } else {
                $archivo->move(public_path('img'), $nombreArchivo);
                $urlTemporal = asset($rutaRelativa);
            }

            $producto->imagen = $rutaRelativa;
        }

        $producto->estado_producto = $request->boolean('estado_producto');

        foreach (['nombre','tipo','material','color','tamanio','marca'] as $campo) {
            if ($request->filled($campo)) {
                $producto->$campo = $request->input($campo);
            }
        }

        if ($request->has('precio_unitario')) {
            $producto->precio_unitario = $request->input('precio_unitario');
        }
        if ($request->has('piezas')) {
            $producto->piezas = $request->input('piezas');
        }

        $producto->save();

        return redirect()
            ->route('producto.index')
            ->with('success', 'Producto actualizado correctamente.')
            ->with('imagen_url', $urlTemporal);
    }

    public function destroy($id)
    {
        $producto = Producto::find($id);
        if (!$producto) {
            return redirect()->route('producto.index')->with('error', 'Producto no encontrado.');
        }

        $producto->delete();
        
        return redirect()->route('producto.index')->with('success', 'Producto eliminado.');
    }
}