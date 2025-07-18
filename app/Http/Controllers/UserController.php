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
            
            $user->save();

            $user->syncRoles($request->roles);
        return redirect('/user')->with('success', 'Usuario registrado correctamente.');
    }

    public function show(Request $request)
    {
        $id = $request->input('id_user');
        $user = User::find($id);
            
        if (!$user) {
            return redirect()->back()->with('error', 'El user no se encontró.');
        }
        return view('/user/showUser', ['user' => $user]);
    }

    public function edit($id)
    {
        $user = User::find($id);
        $roles = Role::pluck('name','name')->all();
        $userRoles = $user->roles->pluck('name','name')->all();

        return view('/user/editUser', [
            'user' => $user,
            'roles' => $roles,
            'userRoles' => $userRoles
        ]);
    }

    public function update(Request $request, User $user)
    {
        // $request->validate([
        //     'name' => 'required|string|max:255',
        //     'email' => 'required|email|max:255|unique:users,email',
        //     'password' => 'required|string|min:8|max:20',
        //     'genero' => 'required',
        //     'edad' => 'required',
        //     'telefono' => 'required',
        //     'direccion' => 'required',
        //     'nombre_usuario' => 'required',
        //     'roles' => 'required'
        // ]);
        
        $user = User::find($user->id_user);
        
        if (!$user) {
            return redirect()->route('user.index')->with('error', 'El user no se encontró.');
        }
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->password = Hash::make($request->input('password'));
        $user->genero = $request->input('genero');
        $user->edad = $request->input('edad');
        $user->telefono = $request->input('telefono');
        $user->direccion = $request->input('direccion');
        $user->save();

        if($request->has('roles')){
            $user->syncRoles($request->roles);
        }

        return redirect()->route('user.index')->with('success', 'El usuario se ha actualizado con éxito.');
    }

    
    public function destroy(User $user, Pedido $pedido, Credito $credito, Abono $abono)
    {
        $user = User::find($user->id_user);

        if ($user->pedidos()->exists()) {
            // Retornar con un mensaje amigable
            return redirect()->back()->with('error', 'No se puede eliminar el usuario porque tiene pedidos asociados.');
        }

        if ($user->creditos()->exists()) {
            // Retornar con un mensaje amigable
            return redirect()->back()->with('error', 'No se puede eliminar el usuario porque tiene creditos asociados.');
        }

        if ($user->abonos()->exists()) {
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