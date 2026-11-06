<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\ServerController;
use App\Http\Middleware\SecurityLoginGuard;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/servidor', [ServerController::class, 'index'])->name('servidor');

/*
|--------------------------------------------------------------------------
| Autenticación
|--------------------------------------------------------------------------
*/

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])
    ->middleware(SecurityLoginGuard::class)
    ->name('login.attempt');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Monitoreo de autenticaciones
|--------------------------------------------------------------------------
*/

Route::get('/panel', MonitoringController::class)
    ->middleware('auth')
    ->name('panel');
