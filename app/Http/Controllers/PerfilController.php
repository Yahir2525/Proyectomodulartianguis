<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class PerfilController extends Controller
{
    // Mostrar perfil del usuario actual
    public function index()
    {
        $user = Auth::user();
        return view('perfil.perfilIndex', compact('user'));
    }

    // Mostrar formulario para editar perfil del usuario actual
    public function edit()
    {
        $user = Auth::user();
        return view('perfil.editPerfil', compact('user'));
    }

    // Guardar los cambios del perfil
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'nullable|string|max:255',
            'nombre_usuario' => 'nullable|string|max:50|unique:users,nombre_usuario,' . $user->id_user . ',id_user',
            'email' => 'nullable|email|max:255|unique:users,email,' . $user->id_user . ',id_user',
            'password' => 'nullable|string|min:6|confirmed',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:80',
            'edad' => 'nullable|integer|min:0',
            'genero' => 'nullable|in:H,M,O',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Solo actualiza si viene en la petición
        if ($request->filled('name')) $user->name = $request->name;
        if ($request->filled('nombre_usuario')) $user->nombre_usuario = $request->nombre_usuario;
        if ($request->filled('email')) $user->email = $request->email;
        if ($request->filled('password')) $user->password = Hash::make($request->password);
        if ($request->filled('telefono')) $user->telefono = $request->telefono;
        if ($request->filled('direccion')) $user->direccion = $request->direccion;
        if ($request->filled('edad')) $user->edad = $request->edad;
        if ($request->filled('genero')) $user->genero = $request->genero;

        // Guardar imagen si se subió
        if ($request->hasFile('imagen')) {
            $file = $request->file('imagen');
            $filename = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
            $file->move(public_path('img/perfiles'), $filename);
            $user->imagen = 'img/perfiles/' . $filename;
        }

        $user->save();

        return redirect()->route('perfil.perfilIndex')->with('success', 'Perfil actualizado correctamente.');
    }


}
