<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class PerfilController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return view('perfil.perfilIndex', compact('user'));
    }

    public function edit()
    {
        $user = Auth::user();
        return view('perfil.editPerfil', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'            => 'nullable|string|max:255',
            'nombre_usuario'  => 'nullable|string|max:50|unique:users,nombre_usuario,' . $user->id_user . ',id_user',
            'email'           => 'nullable|email|max:255|unique:users,email,' . $user->id_user . ',id_user',
            'current_password'=> 'required_with:password',
            'password'        => [
                'nullable',
                'string',
                'min:8',
                'max:20',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
                'unique:users,password',
            ],
            'telefono'        => 'nullable|string|max:20',
            'direccion'       => 'nullable|string|max:80',
            'edad'            => 'nullable|integer|min:0',
            'genero'          => 'nullable|in:H,M,O',
            'imagen'          => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ], [
            'name.string'            => 'El nombre debe ser texto válido.',
            'name.max'               => 'El nombre no puede tener más de 255 caracteres.',

            'nombre_usuario.string'  => 'El nombre de usuario debe ser texto.',
            'nombre_usuario.max'     => 'El nombre de usuario no puede superar los 50 caracteres.',
            'nombre_usuario.unique'  => 'Ese nombre de usuario ya está en uso.',

            'email.email'            => 'El correo debe tener un formato válido.',
            'email.max'              => 'El correo no puede superar los 255 caracteres.',
            'email.unique'           => 'Ese correo ya está en uso.',

            'current_password.required_with' => 'Ingrese su contraseña actual para poder cambiarla.',

            'password.min'           => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'password.max'           => 'La nueva contraseña no puede superar los 20 caracteres.',
            'password.confirmed'     => 'La confirmación de la contraseña no coincide.',
            'password.regex'         => 'La contraseña debe incluir al menos una mayúscula, una minúscula, un número y un carácter especial.',
            'password.unique'    => 'La contraseña ya está en uso.',

            'telefono.string'        => 'El teléfono debe ser un texto válido.',
            'telefono.max'           => 'El teléfono no puede superar los 20 caracteres.',

            'direccion.string'       => 'La dirección debe ser un texto válido.',
            'direccion.max'          => 'La dirección no puede superar los 80 caracteres.',

            'edad.integer'           => 'La edad debe ser un número.',
            'edad.min'               => 'La edad no puede ser negativa.',

            'genero.in'              => 'El género debe ser H (hombre), M (mujer) u O (otro).',

            'imagen.image'           => 'El archivo debe ser una imagen.',
            'imagen.mimes'           => 'La imagen debe ser jpeg, png, jpg, gif o webp.',
            'imagen.max'             => 'La imagen no puede superar los 2 MB.',
        ]);

        if ($request->filled('name'))           $user->name = $request->name;
        if ($request->filled('nombre_usuario')) $user->nombre_usuario = $request->nombre_usuario;
        if ($request->filled('email'))          $user->email = $request->email;

        if ($request->filled('password')) {
            if (!\Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'La contraseña actual no es correcta.']);
            }

            if (\Hash::check($request->password, $user->password)) {
                return back()->withErrors(['password' => 'La nueva contraseña no puede ser igual a la actual.']);
            }

            $user->password = \Hash::make($request->password);
        }

        if ($request->filled('telefono'))       $user->telefono = $request->telefono;
        if ($request->filled('direccion'))      $user->direccion = $request->direccion;
        if ($request->has('edad'))              $user->edad = $request->edad;   // permite 0
        if ($request->filled('genero'))         $user->genero = $request->genero;

        if ($request->hasFile('imagen')) {
            $file        = $request->file('imagen');
            $filename    = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
            $relative    = 'perfiles/' . $filename;

            if (!empty($user->imagen)) {
                if (config('filesystems.default') === 's3') {
                    try { \Storage::disk('s3')->delete($user->imagen); } catch (\Throwable $e) {}
                } else {
                    $old = public_path($user->imagen);
                    if (is_file($old)) @unlink($old);
                }
            }

            if (config('filesystems.default') === 's3') {
                \Storage::disk('s3')->putFileAs('perfiles', $file, $filename, [
                    'visibility'  => 'private',
                    'ContentType' => $file->getMimeType(),
                ]);
            } else {
                $file->move(public_path('perfiles'), $filename);
            }

            $user->imagen = $relative;
        }

        $user->save();

        return redirect()->route('perfil.perfilIndex')->with('success', 'Perfil actualizado correctamente.');
    }
}
