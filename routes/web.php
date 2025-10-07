<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Toutes les routes de l’application : affichage, API interne, CRUD cartes,
| authentification.
|
*/

// 🔸 Redirige la racine vers la collection
Route::get('/', function () {
    return redirect()->route('cards.index');
});

// 🔸 Zone authentifiée
Route::middleware(['auth'])->group(function () {
    // Alias attendu par Breeze après login -> renvoie vers la collection
    Route::get('/dashboard', function () {
        return redirect()->route('cards.index');
    })->name('dashboard');

    // API interne AJAX
    Route::get('/api/card/{code}', [CardController::class, 'apiFindCard'])->name('api.card.find');

    // CRUD des cartes
    Route::resource('cards', CardController::class);
});

// 🔸 Routes d’authentification Laravel Breeze
require __DIR__ . '/auth.php';
