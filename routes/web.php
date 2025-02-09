<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AbonoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\Compra_productoController;
use App\Http\Controllers\CreditoController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\VendedorController;


Route::get('/', function () {
    return view('admin');
});

Route::get('/login', function () {
    return view('auth.login');
});

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

Route::resource('cliente', ClienteController::class);

Route::resource('compra', CompraController::class);

Route::resource('pedido', PedidoController::class);

Route::resource('credito', CreditoController::class);

Route::resource('producto', ProductoController::class);

Route::resource('vendedor', VendedorController::class);