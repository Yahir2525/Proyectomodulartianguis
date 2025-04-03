<?php

namespace App\Http\Controllers;

// use Illuminate\Support\Facades\Auth;
use App\Models\User;
// use App\Models\Role;
use App\Models\Abono;
use App\Models\Cliente;
use App\Models\Compra;
use App\Models\Credito;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Vendedor;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function showSignupForm()
    {
        return view('user.login');
    }

    public function registerUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => 'required|string|min:6',
        ]);
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        // $role = '1';
        // $user->role()->associate($role);
        $user->save();

        return redirect('/login')->with('success', '¡Registro exitoso! Por favor, inicia sesión.');
    }
     /**
     * Destroy the user's session (logout).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
    //  */
    public function destroy(Request $request)
    {
    //     Auth::logout();

    //     $request->session()->invalidate();

    //     $request->session()->regenerateToken();

    //     return redirect('/login')->with('success', 'Has cerrado sesión correctamente.');
    // }
}
}