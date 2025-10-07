<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DeckController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// deck
Route::middleware(['auth'])->group(function () {
    Route::resource('decks', DeckController::class);
});

Route::resource('decks', DeckController::class);

//selction de carte collection pour le deck
Route::middleware(['auth'])->group(function () {
    Route::resource('decks', DeckController::class);
});
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

    // API interne pour la recherche de cartes
    Route::get('/api/card/{code}', [CardController::class, 'apiFindCard'])->name('api.card.find');

    // CRUD complet sur les cartes
    Route::resource('cards', CardController::class);

    // ✅ Gestion du mot de passe utilisateur
    Route::get('/password/change', [ProfileController::class, 'editPassword'])->name('password.change');
    Route::post('/password/change', [ProfileController::class, 'updatePassword'])->name('password.update');
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
