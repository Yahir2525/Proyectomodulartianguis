<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;   // <- agregado (dataset)
use Illuminate\Support\Facades\Artisan;   // <- agregado (dataset)

// Controladores
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
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\RegistroController;
use App\Http\Controllers\PrediccionController;
use App\Support\MineriaPipeline;

Route::get('/predicciones', function () {
    MineriaPipeline::predecirNiveles();
    return back()->with('success', 'Predicciones generadas.');
})->name('predicciones.predecir');

Route::get('/predicciones/aplicar', function () {
    MineriaPipeline::aplicarPredicciones();
    return back()->with('success', 'Predicciones aplicadas.');
})->name('predicciones.aplicar');

// Página principal
Route::get('/', function () {
    return view('admin');
});

// Autenticación
Route::get('/login', function () {
    return view('auth.login'); // Vista de login personalizada
})->name('login');

Route::post('/login', function (Request $request) {
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
    return redirect('/');
})->name('logout');

// Errores
Route::get('/401', fn() => view('pages.401'));
Route::get('/404', fn() => view('pages.404'));
Route::get('/500', fn() => view('pages.500'));

// Registro
Route::get('/registro', fn() => view('auth.registro'));

// --------------------------------------------
// Rutas protegidas para usuarios autenticados
// --------------------------------------------
Route::middleware(['auth'])->group(function () {
    Route::get('/perfil', [PerfilController::class, 'index'])->name('perfil.perfilIndex');
    Route::get('/perfil/editar', [PerfilController::class, 'edit'])->name('perfil.editPerfil');
    Route::put('/perfil', [PerfilController::class, 'update'])->name('perfil.update');
});

Route::resource('registro', RegistroController::class);


Route::resource('producto', ProductoController::class);
// -------------------------------------------------
// Rutas protegidas por el middleware is_user
// -------------------------------------------------
Route::middleware(['is_user'])->group(function () {

    // Abonos
    Route::resource('abono', AbonoController::class);
    Route::post('/abono/aplicar/{id}', [AbonoController::class, 'aplicarAlCredito'])->name('abono.aplicar');

    // Pedidos
    Route::resource('pedido', PedidoController::class);
    Route::post('/pedido/cerrar/{id_pedido}', [PedidoController::class, 'cerrar'])->name('pedido.cerrar');
    Route::post('/pedido/{id}/reabrir', [PedidoController::class, 'reabrir'])->name('pedido.reabrir');
    Route::get('/pedido/{id}/ticket', [PedidoController::class, 'generarTicket'])->name('pedido.ticket');

    // Créditos
    Route::resource('credito', CreditoController::class);
    Route::post('/credito/crear/{pedido}', [CreditoController::class, 'crearDesdePedido'])->name('credito.crearDesdePedido');

    // Productos
    

    // Carro y productos en carro
    Route::resource('carro', CarroController::class);
    Route::get('/carro/{id_carro}/producto/{id_producto}/edit', [CarroController::class, 'edit'])->name('carro.edit');
    Route::put('/carro/{carro}/producto/{id_producto}', [CarroController::class, 'update'])->name('carro.update');
    Route::post('/carro/agregar-multiples', [CarroController::class, 'agregarMultiples'])->name('carro.agregarMultiples');
    Route::delete('/carro/{id_carro}/producto/{id_producto}', [CarroController::class, 'eliminarProducto'])->name('carro.eliminarProducto');
});

// -------------------------------------------------
// Rutas protegidas por el middleware is_admin
// -------------------------------------------------
Route::middleware(['is_admin'])->group(function () {
    // Permisos y roles
    Route::resource('permission', PermissionController::class);
    Route::resource('role', RoleController::class);
    Route::delete('/role/{role}/permission/{permission}', [RoleController::class, 'destroyPermissionFromRole'])->name('role.permission.destroy');

    // Usuarios
    Route::resource('user', UserController::class);

    // -----------------------
    // DATASET (solo cambios)
    // -----------------------
    // Descargar el CSV ya generado
    // Route::get('/dataset', function () {
    //     $path = 'mineria_dataset.csv';

    //     abort_unless(Storage::disk('public')->exists($path), 404);

    //     return Storage::disk('public')->download($path, 'mineria_dataset.csv', [
    //         'Content-Type'       => 'text/csv; charset=UTF-8',
    //         'Cache-Control'      => 'no-store, no-cache, must-revalidate, max-age=0',
    //         'Pragma'             => 'no-cache',
    //         'Expires'            => '0',
    //         'X-Accel-Buffering'  => 'no',
    //     ]);
    // })->name('dataset.download');

    // Ejecuta el comando y descarga el CSV actualizado
    // Route::post('/dataset/export-download', function () {
    //     Artisan::call('export:dataset-mineria');

    //     $path = 'mineria_dataset.csv';
    //     abort_unless(Storage::disk('public')->exists($path), 404, 'El CSV no se generó.');

    //     return Storage::disk('public')->download($path, 'mineria_dataset.csv', [
    //         'Content-Type'       => 'text/csv; charset=UTF-8',
    //         'Cache-Control'      => 'no-store, no-cache, must-revalidate, max-age=0',
    //         'Pragma'             => 'no-cache',
    //         'Expires'            => '0',
    //         'X-Accel-Buffering'  => 'no',
    //     ]);
    // })->name('dataset.export-download');

    Route::post('/dataset/actualizar', function () {
        MineriaPipeline::exportarDataset();
        return back()->with('success', 'Dataset actualizado correctamente.');
    })->name('dataset.actualizar')->middleware('is_admin');

});
