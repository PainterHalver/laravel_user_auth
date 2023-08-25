<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\OAuthController;

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

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/logout', [HomeController::class, 'logout'])->name('logout');

Route::namespace('Auth')->prefix('auth')->group(function () {
    Route::prefix('oauth')->group(function () {
        Route::get('google/callback', [OAuthController::class, 'handleGoogleCallback'])
            ->name('auth.oauth.google.callback');
    });
});
