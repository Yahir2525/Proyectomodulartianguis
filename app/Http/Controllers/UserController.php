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

    public function index()
    {
        $user = new User ();

        $userIndex = User::all();
        return view('user/userIndex', compact ('userIndex'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('user/createUser');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
            $user = new User();
            $user->name = $request->input('name');
            $user->email = $request->input('email');
            $user->password = bcrypt($request->input('password'));
            $user->genero = $request->input('genero');
            $user->edad = $request->input('edad');
            $user->telefono = $request->input('telefono');
            $user->direccion = $request->input('direccion');
            $user->nombre_usuario = $request->input('nombre_usuario');
            
            $user->save();
        return redirect('/user')->with('success', 'Usuario registrado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        $id = $request->input('id_user');
        $user = User::find($id);
            
        if (!$user) {
            return redirect()->back()->with('error', 'El user no se encontró.');
        }
        return view('/user/showUser', ['user' => $user]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $user = User::find($id);

        if (!$user) {
            return redirect()->back()->with('error', 'El usuario no se encontró.');
                // return redirect()->route('/producto/productoIndex')->with('error', 'El producto no se encontró.');
        }
        return view('/user/editUsuario', ['user' => $user]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $user = User::find($id);
    
        if (!$user) {
            return redirect()->route('user.userIndex')->with('error', 'El user no se encontró.');
        }
        $user = new User();
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->password = bcrypt($request->input('password'));
        $user->genero = $request->input('genero');
        $user->edad = $request->input('edad');
        $user->telefono = $request->input('telefono');
        $user->direccion = $request->input('direccion');
        $user->nombre_usuario = $request->input('nombre_usuario');
        $user->save();
        return redirect()->route('user.userIndex')->with('success', 'El usuario se ha actualizado con éxito.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return redirect()->route('user.index')->with('error', 'El user no se encontró.');
        }

        // $compras = Compra::where('nombre_usuario', $id)->get();

        // foreach ($compras as $item) {
        //     $compra = Compra::find($item->id_compra);
        //     if ($compra) {
        //         $compra->delete();
        //     }
        // }

        // $creditos = Credito::where('nombre_usuario', $id)->get();

        // foreach ($creditos as $item) {
        //     $credito = Credito::find($item->id_credito);
        //     if ($credito) {
        //         $credito->delete();
        //     }
        // }

        $user->delete();


        return redirect()->route('user.index')->with('success', 'El usuario se ha eliminado con éxito.');
    }
}


//     public function showSignupForm()
//     {
//         return view('user.login');
//     }

//     public function registerUser(Request $request)
//     {
//         $request->validate([
//             'name' => 'required|string|max:255',
//             'email' => 'required|email|unique:users,email|max:255',
//             'password' => 'required|string|min:6',
//         ]);
//         User::create([
//             'name' => $request->name,
//             'email' => $request->email,
//             'password' => bcrypt($request->password),
//         ]);

//         // $role = '1';
//         // $user->role()->associate($role);
//         $user->save();

//         return redirect('/login')->with('success', '¡Registro exitoso! Por favor, inicia sesión.');
//     }
//      /**
//      * Destroy the user's session (logout).
//      *
//      * @param  \Illuminate\Http\Request  $request
//      * @return \Illuminate\Http\RedirectResponse
//     //  */
//     public function destroy(Request $request)
//     {
//     //     Auth::logout();

//     //     $request->session()->invalidate();

//     //     $request->session()->regenerateToken();

//     //     return redirect('/login')->with('success', 'Has cerrado sesión correctamente.');
//     // }
// }
// }