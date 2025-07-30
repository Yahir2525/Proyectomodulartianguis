<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class RegistroController extends Controller
{
    public function index()
    {
        return view('auth.registro');
    }
    public function create()
    {
        return view('auth.registro');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nombre_usuario' => 'required|string|max:50|unique:users,nombre_usuario',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|confirmed|min:6',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'nombre_usuario' => $validated['nombre_usuario'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);
        

        $user->assignRole('user');

        Auth::login($user); // para que entre directo tras registrarse

        return redirect()->route('perfil.editPerfil')->with('success', 'Bienvenido. Completa tu perfil.');
    }
}
