<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


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
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/'
            ],
            'telefono'        => 'nullable|string|max:20',
            'direccion'       => 'nullable|string|max:80',
            'edad'            => 'nullable|integer|min:0',
            'genero'          => 'nullable|in:H,M,O',
            'imagen'          => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($request->filled('name'))           $user->name = $request->name;
        if ($request->filled('nombre_usuario')) $user->nombre_usuario = $request->nombre_usuario;
        if ($request->filled('email'))          $user->email = $request->email;

        // --- Cambio de contraseña ---
        if ($request->filled('password')) {
            // Verificar contraseña actual
            if (!\Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'La contraseña actual no es correcta.']);
            }

            // Evitar que sea la misma contraseña
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

            // Borrar anterior
            if (!empty($user->imagen)) {
                if (config('filesystems.default') === 's3') {
                    try { \Storage::disk('s3')->delete($user->imagen); } catch (\Throwable $e) {}
                } else {
                    $old = public_path($user->imagen);
                    if (is_file($old)) @unlink($old);
                }
            }

            // Subir nueva
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

        return redirect()->route('perfil.perfilIndex')
            ->with('success', 'Perfil actualizado correctamente.');
    }



}
