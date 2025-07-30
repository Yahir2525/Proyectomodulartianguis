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
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
// use App\Http\Middleware\AdminRole;

class UserController extends Controller
{
    public function index()
    {
        $userIndex = User::all();
        return view('user/userIndex', compact ('userIndex'));
    }

    public function create()
    {
        $roles = Role::pluck('name','name')->all();
        return view('user/createUser', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|max:20',
            'genero' => 'required',
            'edad' => 'required',
            'telefono' => 'required',
            'direccion' => 'required',
            'nombre_usuario' => 'required',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'roles' => 'required'
        ]);
            $user = new User();
            $user->name = $request->input('name');
            $user->email = $request->input('email');
            $user->password = Hash::make($request->input('password'));
            $user->genero = $request->input('genero');
            $user->edad = $request->input('edad');
            $user->telefono = $request->input('telefono');
            $user->direccion = $request->input('direccion');
            $user->nombre_usuario = $request->input('nombre_usuario');
            if ($request->hasFile('imagen')) {
                $file = $request->file('imagen');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('img/perfiles'), $filename);
                $user->imagen = 'img/perfiles/' . $filename;
            }

            
            $user->save();

            $user->syncRoles($request->roles);
        return redirect('/user')->with('success', 'Usuario registrado correctamente.');
    }

    public function show(Request $request)
    {
        $busqueda = $request->input('id_user');

        if (is_numeric($busqueda)) {
            $usuarios = User::where('id_user', $busqueda)->get();
        } else {
            $usuarios = User::where('nombre_usuario', 'ILIKE', '%' . $busqueda . '%')->get();
        }

        if ($usuarios->isEmpty()) {
            return redirect()->back()->with('error', 'No se encontraron usuarios.');
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

        // Validación de campos (nullable excepto imagen con restricciones)
        $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:users,email,' . $user->id_user . ',id_user',
            'password' => 'nullable|string|min:8|max:20|confirmed',
            'genero' => 'nullable|in:H,M,O',
            'edad' => 'nullable|integer|min:0',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:80',
            'nombre_usuario' => 'nullable|string|max:50|unique:users,nombre_usuario,' . $user->id_user . ',id_user',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'roles' => 'nullable|array',
        ]);

        // Asignación de campos si vienen en la solicitud
        if ($request->filled('name')) $user->name = $request->name;
        if ($request->filled('email')) $user->email = $request->email;
        if ($request->filled('password')) $user->password = Hash::make($request->password);
        if ($request->filled('genero')) $user->genero = $request->genero;
        if ($request->filled('edad')) $user->edad = $request->edad;
        if ($request->filled('telefono')) $user->telefono = $request->telefono;
        if ($request->filled('direccion')) $user->direccion = $request->direccion;
        if ($request->filled('nombre_usuario')) $user->nombre_usuario = $request->nombre_usuario;

        // Carga y almacenamiento de imagen
        if ($request->hasFile('imagen')) {
            $file = $request->file('imagen');
            $filename = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
            $file->move(public_path('perfiles'), $filename);
            $user->imagen = 'perfiles/' . $filename;
        }

        $user->save();

        // Asignar roles si se enviaron
        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        }

        return redirect()->route('user.index')->with('success', 'El usuario se ha actualizado con éxito.');
    }


    
    public function destroy(User $user, Pedido $pedido, Credito $credito, Abono $abono)
    {
        $user = User::find($user->id_user);

        if ($user->pedido()->exists()) {
            // Retornar con un mensaje amigable
            return redirect()->back()->with('error', 'No se puede eliminar el usuario porque tiene pedidos asociados.');
        }

        if ($user->credito()->exists()) {
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