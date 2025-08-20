<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductoController extends Controller
{
    public function index()
    {
        $usuario = Auth::user();

        // Productos visibles
        if ($usuario && $usuario->hasRole('administrador')) {
            $productoIndex = Producto::all();
        } else {
            $productoIndex = Producto::where('estado_producto', true)->get();
        }

        // Usuarios y pedidos solo si hay usuario logueado
        $usuarios = collect();
        $pedidosUsuario = collect();

        if ($usuario) {
            if ($usuario->hasRole('administrador')) {
                $usuarios = \App\Models\User::all();
                $pedidosUsuario = \App\Models\Pedido::with('user')->get();
            } else {
                $pedidosUsuario = \App\Models\Pedido::where('id_user', $usuario->id_user)->get();
            }
        }

        return view('producto.productoIndex', compact('productoIndex', 'pedidosUsuario', 'usuarios'));
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
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'nombre' => ['required', 'string'],
            // ...tus demás reglas
        ]);

        if ($request->hasFile('imagen')) {
            $file        = $request->file('imagen');
            $filename    = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
            $relative    = 'img/' . $filename;  // misma convención en toda la app

            // Borrar anterior
            if (!empty($user->imagen)) {
                if (config('filesystems.default') === 's3') {
                    try { Storage::disk('s3')->delete($producto->imagen); } catch (\Throwable $e) {}
                } else {
                    $old = public_path($producto->imagen);
                    if (is_file($old)) @unlink($old);
                }
            }

            // Subir nueva
            if (config('filesystems.default') === 's3') {
                Storage::disk('s3')->putFileAs('img', $file, $filename, [
                    'visibility'  => 'private',                 // bucket privado
                    'ContentType' => $file->getMimeType(),
                ]);
            } else {
                $file->move(public_path('img'), $filename);
            }

            $producto->imagen = $relative; // la vista usará $user->imagen_url (accessor)
        }

        // Campos normales
        $producto->nombre = $request->input('nombre');
        $producto->tipo = $request->input('tipo');
        $producto->material = $request->input('material');
        $producto->color = $request->input('color');
        $producto->tamanio = $request->input('tamanio');
        $producto->marca = $request->input('marca');
        $producto->precio_unitario = $request->input('precio_unitario');
        $producto->piezas = $request->input('piezas');
        $producto->estado_producto = $request->input('estado_producto', true); // por default activo
        $producto->save();

        return redirect('/producto')->with('success', 'Producto registrado correctamente.');
    }

    public function show(Request $request)
    {
        $busqueda = $request->input('busqueda');

        $productos = collect();
        if (is_numeric($busqueda)) {
            $producto = \App\Models\Producto::find($busqueda);
            if ($producto) {
                $productos->push($producto);
            }
        } elseif ($busqueda) {
            $productos = \App\Models\Producto::where('nombre', 'ILIKE', "%$busqueda%")->get();
        }

        $usuarios = \App\Models\User::all();
        $pedidosUsuario = \App\Models\Pedido::with('user')->get();

        return view('producto.showProducto', compact('productos', 'usuarios', 'pedidosUsuario'));
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
        $producto = \App\Models\Producto::find($id);
        if (!$producto) {
            return redirect()->route('producto.index')->with('error', 'Producto no encontrado.');
        }

        // (opcional) validaciones
        $request->validate([
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            // agrega aquí tus reglas para los demás campos si quieres
        ]);

        $urlTemporal = null; // si suben imagen a S3 privado, la generamos

        // Si subieron una imagen, la actualizamos
        if ($request->hasFile('imagen')) {
            $archivo       = $request->file('imagen');
            $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
            $rutaRelativa  = 'img/' . $nombreArchivo; // seguimos guardando "img/xxx.ext" en DB

            // 1) Borra la imagen anterior según el disk activo
            if ($producto->imagen) {
                if (config('filesystems.default') === 's3') {
                    try { Storage::disk('s3')->delete($producto->imagen); } catch (\Throwable $e) {}
                } else {
                    $rutaFisica = public_path($producto->imagen);
                    if (is_file($rutaFisica)) @unlink($rutaFisica);
                }
            }

            // 2) Sube la nueva imagen
            if (config('filesystems.default') === 's3') {
                // bucket privado
                Storage::disk('s3')->putFileAs('img', $archivo, $nombreArchivo, [
                    'visibility'  => 'private',
                    'ContentType' => $archivo->getMimeType(),
                ]);

                // URL temporal (válida 10 min) para mostrar en la vista si quieres
                $urlTemporal = Storage::disk('s3')->temporaryUrl($rutaRelativa, now()->addMinutes(10));
            } else {
                // entorno local: public/img
                $archivo->move(public_path('img'), $nombreArchivo);
                $urlTemporal = asset($rutaRelativa); // por si quieres previsualizar tras actualizar
            }

            $producto->imagen = $rutaRelativa; // guardamos misma convención en DB
        }

        // Checkbox a booleano real
        $producto->estado_producto = $request->boolean('estado_producto');

        // Campos de texto (solo si vienen y no están vacíos)
        foreach (['nombre','tipo','material','color','tamanio','marca'] as $campo) {
            if ($request->filled($campo)) {
                $producto->$campo = $request->input($campo);
            }
        }

        // Numéricos con 'has' para permitir 0
        if ($request->has('precio_unitario')) {
            $producto->precio_unitario = $request->input('precio_unitario');
        }
        if ($request->has('piezas')) {
            $producto->piezas = $request->input('piezas');
        }

        $producto->save();

        // Si generamos URL temporal (S3 privado o local), la mandamos por sesión para mostrar previsualización
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
