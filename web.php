<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\TokoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login',[LoginController::class,'index'])->name('login');
Route::post('/login',[LoginController::class,'authenticate'])->name('login.post');
Route::get('/logout',[LoginController::class,'logout'])->name('logout');

Route::get('/dashboard',[DashboardController::class,'index']);
Route::get('/toko',[TokoController::class,'index']);

Route::resource('pelanggan',LoginController::class)->except(['show']);
Route::get('/indexcreate', [LoginController::class, 'indexcreate'])->name('pelanggan.indexcreate');
Route::get('/register', [LoginController::class, 'register'])->name('pelanggan.register');
Route::post('/update', [LoginController::class, 'update'])->name('pelanggan.update');

Route::resource('produk',ProdukController::class)->except(['show']);
Route::post('/update', [ProdukController::class, 'update'])->name('produk.update');
