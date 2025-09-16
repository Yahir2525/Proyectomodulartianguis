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
            'name'           => ['required','string','max:255'],
            'email'          => ['required','email','max:255','unique:users,email'],
            'password'       => [
                'required',
                'string',
                'min:8',
                'max:20',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*?&]/',
                'unique:users,password',
            ],
            'genero'         => ['required','in:H,M,O'],
            'edad'           => ['required','integer','min:0'],
            'telefono'       => ['required','string','max:20'],
            'direccion'      => ['required','string','max:255'],
            'nombre_usuario' => ['required','string','max:50','unique:users,nombre_usuario'],
            'imagen'         => ['nullable','image','mimes:jpeg,png,jpg,gif,webp','max:2048'],
            'roles'          => ['required'],
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'name.string'   => 'El nombre debe ser texto válido.',
            'name.max'      => 'El nombre no puede superar los 255 caracteres.',

            'email.required' => 'El correo es obligatorio.',
            'email.email'    => 'El correo debe tener un formato válido.',
            'email.max'      => 'El correo no puede superar los 255 caracteres.',
            'email.unique'   => 'Este correo ya está en uso.',

            'password.required' => 'La contraseña es obligatoria.',
            'password.string'   => 'La contraseña debe ser texto válido.',
            'password.min'      => 'La contraseña debe tener al menos 8 caracteres.',
            'password.max'      => 'La contraseña no puede tener más de 20 caracteres.',
            'password.regex'    => 'La contraseña debe incluir al menos una mayúscula, una minúscula, un número y un carácter especial.',
            'password.unique'    => 'La contraseña ya está en uso.',
            
            'genero.required' => 'El género es obligatorio.',
            'genero.in'       => 'El género seleccionado no es válido.',

            'edad.required' => 'La edad es obligatoria.',
            'edad.integer'  => 'La edad debe ser un número entero.',
            'edad.min'      => 'La edad no puede ser negativa.',

            'telefono.required' => 'El teléfono es obligatorio.',
            'telefono.string'   => 'El teléfono debe ser texto válido.',
            'telefono.max'      => 'El teléfono no puede superar los 20 caracteres.',

            'direccion.required' => 'La dirección es obligatoria.',
            'direccion.string'   => 'La dirección debe ser texto válido.',
            'direccion.max'      => 'La dirección no puede superar los 255 caracteres.',

            'nombre_usuario.required' => 'El nombre de usuario es obligatorio.',
            'nombre_usuario.string'   => 'El nombre de usuario debe ser texto válido.',
            'nombre_usuario.max'      => 'El nombre de usuario no puede superar los 50 caracteres.',
            'nombre_usuario.unique'   => 'Este nombre de usuario ya está en uso.',

            'imagen.image' => 'El archivo debe ser una imagen.',
            'imagen.mimes' => 'La imagen debe ser jpeg, png, jpg, gif o webp.',
            'imagen.max'   => 'La imagen no puede pesar más de 2 MB.',

            'roles.required' => 'Debe asignar al menos un rol al usuario.',
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
        $user->nivel_usuario  = 'excelente';
        $user->dias_aplazo    = 1;

        if ($request->hasFile('imagen')) {
            $archivo = $request->file('imagen');
            $nombreArchivo = time().'_'.$archivo->getClientOriginalName();
            $rutaRelativa = 'perfiles/'.$nombreArchivo;

            if (config('filesystems.default') === 's3') {
                Storage::disk('s3')->putFileAs('perfiles', $archivo, $nombreArchivo, [
                    'visibility'  => 'public',
                    'ContentType' => $archivo->getMimeType(),
                ]);
                $user->imagen = $rutaRelativa;
            } else {
                $archivo->move(public_path('perfiles'), $nombreArchivo);
                $user->imagen = $rutaRelativa;
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

        if (is_numeric($busqueda)) {
            $usuarios = User::where('id_user', 'LIKE', "%{$busqueda}%")->get();

            if ($usuarios->isEmpty()) {
                return back()->with('error', 'No se encontraron usuarios con ese ID.');
            }

            return view('user.showUser', compact('usuarios'));
        }

        $usuarios = User::where('nombre_usuario', 'ILIKE', "%{$busqueda}%")->get();

        if ($usuarios->isEmpty()) {
            return back()->with('error', 'No se encontraron usuarios con ese nombre.');
        }

        return view('user.showUser', compact('usuarios'));
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
            'password'        => [
                'nullable',
                'string',
                'min:8',
                'max:20',
                'confirmed',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*?&]/',
                'unique:users,password',
                function ($attribute, $value, $fail) use ($user) {
                    if (!empty($value) && Hash::check($value, $user->password)) {
                        $fail('La nueva contraseña no puede ser igual a la actual.');
                    }
                }
            ],
            'genero'          => 'nullable|in:H,M,O',
            'edad'            => 'nullable|integer|min:0',
            'telefono'        => 'nullable|string|max:20',
            'direccion'       => 'nullable|string|max:80',
            'nombre_usuario'  => 'nullable|string|max:50|unique:users,nombre_usuario,' . $user->id_user . ',id_user',
            'imagen'          => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'roles'           => 'nullable|array',
        ], [
            'name.string' => 'El nombre debe ser texto válido.',
            'name.max'    => 'El nombre no puede superar los 255 caracteres.',

            'email.email'    => 'El correo debe tener un formato válido.',
            'email.max'      => 'El correo no puede superar los 255 caracteres.',
            'email.unique'   => 'Este correo ya está en uso.',

            'password.string'    => 'La contraseña debe ser texto válido.',
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres.',
            'password.max'       => 'La contraseña no puede tener más de 20 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.regex'     => 'La contraseña debe incluir al menos una mayúscula, una minúscula, un número y un carácter especial.',
            'password.unique'    => 'La contraseña ya está en uso.',

            'genero.in' => 'El género seleccionado no es válido.',

            'edad.integer' => 'La edad debe ser un número entero.',
            'edad.min'     => 'La edad no puede ser negativa.',

            'telefono.string' => 'El teléfono debe ser texto válido.',
            'telefono.max'    => 'El teléfono no puede superar los 20 caracteres.',

            'direccion.string' => 'La dirección debe ser texto válido.',
            'direccion.max'    => 'La dirección no puede superar los 80 caracteres.',

            'nombre_usuario.string' => 'El nombre de usuario debe ser texto válido.',
            'nombre_usuario.max'    => 'El nombre de usuario no puede superar los 50 caracteres.',
            'nombre_usuario.unique' => 'Este nombre de usuario ya está en uso.',

            'imagen.image' => 'El archivo debe ser una imagen.',
            'imagen.mimes' => 'La imagen debe ser jpeg, png, jpg, gif o webp.',
            'imagen.max'   => 'La imagen no puede pesar más de 2 MB.',
        ]);

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

        if ($request->hasFile('imagen')) {
            $file        = $request->file('imagen');
            $filename    = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
            $relative    = 'perfiles/' . $filename;

            if (!empty($user->imagen)) {
                if (config('filesystems.default') === 's3') {
                    try { Storage::disk('s3')->delete($user->imagen); } catch (\Throwable $e) {}
                } else {
                    $old = public_path($user->imagen);
                    if (is_file($old)) @unlink($old);
                }
            }

            if (config('filesystems.default') === 's3') {
                Storage::disk('s3')->putFileAs('perfiles', $file, $filename, [
                    'visibility'  => 'private',
                    'ContentType' => $file->getMimeType(),
                ]);
            } else {
                $file->move(public_path('perfiles'), $filename);
            }

            $user->imagen = $relative;
        }

        $user->save();

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
            return redirect()->back()->with('error', 'No se puede eliminar el usuario porque tiene pedidos asociados.');
        }

        if ($user->creditos()->exists()) {
            return redirect()->back()->with('error', 'No se puede eliminar el usuario porque tiene creditos asociados.');
        }

        if ($user->abono()->exists()) {
            return redirect()->back()->with('error', 'No se puede eliminar el usuario porque tiene abonos asociadas.');
        }

        if (!$user) {
            return redirect()->route('user.index')->with('error', 'El user no se encontró.');
        }

        $user->delete();

        return redirect()->route('user.index')->with('success', 'El usuario se ha eliminado con éxito.');
    }
}