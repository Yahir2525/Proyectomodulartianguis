<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AbonoController;
use App\Http\Controllers\CarroController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\Compra_productoController;
use App\Http\Controllers\CreditoController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\VendedorController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
// use App\Http\Middleware\AdminRole;

Route::get('/', function () {
    return view('admin');
});

// Route::get('/login', function () {
//     return view('auth.login');
// });

Route::get('/login', function () {
    return view('auth.login'); // o tu vista personalizada
})->name('login');

Route::post('/login', function (\Illuminate\Http\Request $request) {
    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials, $request->remember)) {
        $request->session()->regenerate();
        return redirect()->intended('/');
    }

    return back()->with('error', 'Correo o contraseña incorrectos');
});


Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/'); // O donde tú quieras redirigir después de cerrar sesión
})->name('logout');

Route::get('/401', function () {
    return view('pages.401');
});

Route::get('/404', function () {
    return view('pages.404');
});

Route::get('/500', function () {
    return view('pages.500');
});

Route::get('/registro', function () {
    return view('auth.registro');
});

Route::resource('abono', AbonoController::class);

// Route::resource('user', UserController::class);

Route::resource('compra', CompraController::class);

Route::resource('pedido', PedidoController::class);

Route::resource('credito', CreditoController::class);

Route::resource('producto', ProductoController::class);

Route::resource('vendedor', VendedorController::class);

Route::resource('carro', CarroController::class);
// Route::resource('role', RoleController::class);

// Route::resource('permission', PermissionController::class);

Route::middleware(['is_admin'])->group(function() {

    Route::resource('permission', PermissionController::class);
    Route::resource('role', RoleController::class);
    Route::resource('user', UserController::class);

});

