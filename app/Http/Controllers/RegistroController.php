<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
            'name'           => 'required|string|max:255',
            'nombre_usuario' => 'required|string|max:50|unique:users,nombre_usuario',
            'email'          => 'required|email|max:255|1unique:users,email',
            'password'       => [
                'required',
                'string',
                'confirmed',
                'min:8',
                'max:20',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*?&]/',
                'unique:users,password',
            ],
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'name.string'   => 'El nombre debe ser texto válido.',
            'name.max'      => 'El nombre no puede superar los 255 caracteres.',

            'nombre_usuario.required' => 'El nombre de usuario es obligatorio.',
            'nombre_usuario.string'   => 'El nombre de usuario debe ser texto válido.',
            'nombre_usuario.max'      => 'El nombre de usuario no puede superar los 50 caracteres.',
            'nombre_usuario.unique'   => 'Este nombre de usuario ya está en uso.',

            'email.required' => 'El correo es obligatorio.',
            'email.email'    => 'El correo debe tener un formato válido.',
            'email.max'      => 'El correo no puede superar los 255 caracteres.',
            'email.unique'   => 'Este correo ya está en uso.',

            'password.required'  => 'La contraseña es obligatoria.',
            'password.string'    => 'La contraseña debe ser texto válido.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres.',
            'password.max'       => 'La contraseña no puede tener más de 20 caracteres.',
            'password.regex'     => 'La contraseña debe incluir al menos una minúscula, una mayúscula, un número y un carácter especial (@$!%*?&).',
            'password.unique'    => 'La contraseña ya está en uso.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'nombre_usuario' => $validated['nombre_usuario'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole('user');

        Auth::login($user);

        return redirect()->route('perfil.editPerfil')->with('success', 'Bienvenido. Completa tu perfil.');
    }
}
