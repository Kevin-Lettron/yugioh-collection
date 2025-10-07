<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 🔸 Page d'accueil
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('cards.index');
    }
    return view('welcome');
})->name('home');

// 🔸 Zone authentifiée
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route('cards.index');
    })->name('dashboard');

    Route::get('/api/card/{code}', [CardController::class, 'apiFindCard'])->name('api.card.find');

    Route::resource('cards', CardController::class);
});

// 🔸 Routes d’authentification Breeze
require __DIR__ . '/auth.php';

// 🔸 ✅ Redéfinition forcée de la route /login (vue combinée)
Route::get('/login', function () {
    if (auth()->check()) {
        return redirect()->route('cards.index');
    }
    return view('auth.login-register');
})->name('login');
