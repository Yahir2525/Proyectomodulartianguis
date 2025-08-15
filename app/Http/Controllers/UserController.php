<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Abono;
use App\Models\Carro;
use App\Models\CarroProducto;
use App\Models\Credito;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
// use App\Http\Middleware\AdminRole;

class UserController extends Controller
{
    public function index()
    {
        $userIndex = User::all();
        $usuarios = User::select('id_user', 'nombre_usuario')->get();

        return view('user.userIndex', compact('userIndex', 'usuarios'));
    }

    public function create()
    {
        $roles = Role::pluck('name','name')->all();
        return view('user/createUser', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|max:255|unique:users,email',
            'password'        => 'required|string|min:8|max:20',
            'genero'          => 'required',
            'edad'            => 'required',
            'telefono'        => 'required',
            'direccion'       => 'required',
            'nombre_usuario'  => 'required',
            'imagen'          => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'roles'           => 'required',
        ]);

        $user = new User();
        $user->name           = $request->input('name');
        $user->email          = $request->input('email');
        $user->password       = Hash::make($request->input('password'));
        $user->genero         = $request->input('genero');
        $user->edad           = $request->input('edad');
        $user->telefono       = $request->input('telefono');
        $user->direccion      = $request->input('direccion');
        $user->nombre_usuario = $request->input('nombre_usuario');
        $user->nivel_usuario  = 'bueno';
        $user->dias_aplazo    = 0;

        // Imagen de perfil (S3 privado o local)
        if ($request->hasFile('imagen')) {
            $archivo = $request->file('imagen');
            $nombreArchivo = time().'_'.$archivo->getClientOriginalName();
            $rutaRelativa = 'perfiles/'.$nombreArchivo; // lo mismo que ya guardabas en local

            if (config('filesystems.default') === 's3') {
                // Sube a S3 en la carpeta "img" con el mismo nombre
                Storage::disk('s3')->putFileAs('perfiles', $archivo, $nombreArchivo, [
                    'visibility'  => 'public',                 // o 'private' si prefieres presigned
                    'ContentType' => $archivo->getMimeType(),
                ]);
                $user->imagen = $rutaRelativa;          // guardas "img/xxx.jpg" como antes
            } else {
                // Comportamiento local original
                $archivo->move(public_path('perfiles'), $nombreArchivo);
                $user->imagen = $rutaRelativa;          // "img/xxx.jpg"
            }
        }

        $user->save();
        $user->syncRoles($request->roles);

        return redirect('/user')->with('success', 'Usuario registrado correctamente.');
    }


    public function show(Request $request)
    {
        $busqueda = $request->input('busqueda');

        if (!$busqueda) {
            return back()->with('error', 'Debes ingresar un ID o un nombre de usuario.');
        }

        // Si es numérico → búsqueda por ID
        if (is_numeric($busqueda)) {
            $usuario = User::find($busqueda);

            if (!$usuario) {
                return back()->with('error', 'Usuario no encontrado por ID.');
            }

            return view('user.showUser', ['usuarios' => collect([$usuario])]);
        }

        // Si es texto → búsqueda por nombre de usuario parcial (ej. "car" → carlos, carmen)
        $usuarios = User::where('nombre_usuario', 'ILIKE', '%' . $busqueda . '%')->get();

        if ($usuarios->isEmpty()) {
            return back()->with('error', 'No se encontraron usuarios con ese nombre.');
        }

        return view('user.showUser', ['usuarios' => $usuarios]);
    }

    public function edit($id)
    {
        $user = User::find($id);
        $roles = Role::pluck('name','name')->all();
        $userRoles = $user->roles->pluck('name','name')->all();

        return view('user.editUser', [
            'user' => $user,
            'roles' => $roles,
            'userRoles' => $userRoles
        ]);
    }

    public function update(Request $request, User $user)
    {
        $user = User::findOrFail($user->id_user);

        $request->validate([
            'name'            => 'nullable|string|max:255',
            'email'           => 'nullable|email|max:255|unique:users,email,' . $user->id_user . ',id_user',
            'password'        => 'nullable|string|min:8|max:20|confirmed',
            'genero'          => 'nullable|in:H,M,O',
            'edad'            => 'nullable|integer|min:0',
            'telefono'        => 'nullable|string|max:20',
            'direccion'       => 'nullable|string|max:80',
            'nombre_usuario'  => 'nullable|string|max:50|unique:users,nombre_usuario,' . $user->id_user . ',id_user',
            'imagen'          => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'roles'           => 'nullable|array',
        ]);

        // Asignaciones (nota: para numéricos usa has() para permitir 0)
        if ($request->filled('name'))            $user->name = $request->name;
        if ($request->filled('email'))           $user->email = $request->email;
        if ($request->filled('password'))        $user->password = Hash::make($request->password);
        if ($request->filled('genero'))          $user->genero = $request->genero;
        if ($request->has('edad'))               $user->edad = $request->edad;
        if ($request->filled('telefono'))        $user->telefono = $request->telefono;
        if ($request->filled('direccion'))       $user->direccion = $request->direccion;
        if ($request->filled('nombre_usuario'))  $user->nombre_usuario = $request->nombre_usuario;

        if ($request->filled('nivel_usuario')) {
            $user->nivel_usuario = $request->nivel_usuario;

            // Solo actualizar dias_aplazo si NO se ingresó manualmente
            if (!$request->filled('dias_aplazo')) {
                switch ($user->nivel_usuario) {
                    case 'excelente': $user->dias_aplazo = 1; break;
                    case 'bueno':     $user->dias_aplazo = 0; break;
                    case 'malo':      $user->dias_aplazo = 0; break;
                }
            }
        }
        if ($request->filled('dias_aplazo')) {
            $user->dias_aplazo = $request->dias_aplazo;
        }

        // Imagen (S3 privado o local) - guardamos SIEMPRE la ruta relativa en BD
        if ($request->hasFile('imagen')) {
            $file        = $request->file('imagen');
            $filename    = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
            $relative    = 'perfiles/' . $filename;  // misma convención en toda la app

            // Borrar anterior
            if (!empty($user->imagen)) {
                if (config('filesystems.default') === 's3') {
                    try { Storage::disk('s3')->delete($user->imagen); } catch (\Throwable $e) {}
                } else {
                    $old = public_path($user->imagen);
                    if (is_file($old)) @unlink($old);
                }
            }

            // Subir nueva
            if (config('filesystems.default') === 's3') {
                Storage::disk('s3')->putFileAs('perfiles', $file, $filename, [
                    'visibility'  => 'private',                 // bucket privado
                    'ContentType' => $file->getMimeType(),
                ]);
            } else {
                $file->move(public_path('perfiles'), $filename);
            }

            $user->imagen = $relative; // la vista usará $user->imagen_url (accessor)
        }

        $user->save();

        // Roles (si se enviaron)
        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        }

        return redirect()->route('user.index')
            ->with('success', 'El usuario se ha actualizado con éxito.');
    }


    
    public function destroy(User $user, Pedido $pedido, Credito $credito, Abono $abono)
    {
        $user = User::find($user->id_user);

        if ($user->pedido()->exists()) {
            // Retornar con un mensaje amigable
            return redirect()->back()->with('error', 'No se puede eliminar el usuario porque tiene pedidos asociados.');
        }

        if ($user->creditos()->exists()) {
            // Retornar con un mensaje amigable
            return redirect()->back()->with('error', 'No se puede eliminar el usuario porque tiene creditos asociados.');
        }

        if ($user->abono()->exists()) {
            // Retornar con un mensaje amigable
            return redirect()->back()->with('error', 'No se puede eliminar el usuario porque tiene abonos asociadas.');
        }

        if (!$user) {
            return redirect()->route('user.index')->with('error', 'El user no se encontró.');
        }

        $user->delete();

        return redirect()->route('user.index')->with('success', 'El usuario se ha eliminado con éxito.');
    }

}