<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\CardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DeckController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 🔸 Page d'accueil
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('cards.index')
        : view('welcome');
})->name('home');

// 🔸 Routes protégées par authentification
Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', function () {
        return redirect()->route('cards.index');
    })->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | CARTES
    |--------------------------------------------------------------------------
    */
    Route::resource('cards', CardController::class);

    // API interne pour la recherche de cartes
    Route::get('/api/card/{code}', [CardController::class, 'apiFindCard'])
        ->name('api.card.find');

    /*
    |--------------------------------------------------------------------------
    | DECKS
    |--------------------------------------------------------------------------
    */
    Route::resource('decks', DeckController::class);

    /*
    |--------------------------------------------------------------------------
    | PROFIL / MOT DE PASSE
    |--------------------------------------------------------------------------
    */
    Route::get('/password/change', [ProfileController::class, 'editPassword'])
        ->name('password.change');
    Route::post('/password/change', [ProfileController::class, 'updatePassword'])
        ->name('password.update');

    /*
    |--------------------------------------------------------------------------
    | UTILISATEURS (Système de suivi)
    |--------------------------------------------------------------------------
    */

    // 🔍 Recherche d'utilisateurs
    Route::get('/users', [UserController::class, 'index'])->name('users.index');

    // 👤 Affichage du profil public
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');

    // 📦 Collection d’un utilisateur
    Route::get('/users/{user}/collection', [UserController::class, 'collection'])->name('users.collection');

    // 🧩 Decks d’un utilisateur
    Route::get('/users/{user}/decks', [UserController::class, 'decks'])->name('users.decks');

    // ➕ Suivre un utilisateur
    Route::post('/users/{user}/follow', [UserController::class, 'follow'])->name('users.follow');

    // ➖ Ne plus suivre un utilisateur
    Route::delete('/users/{user}/unfollow', [UserController::class, 'unfollow'])->name('users.unfollow');

    // 👥 Liste des utilisateurs suivis (abonnements)
    Route::get('/following', [UserController::class, 'following'])->name('users.following');

    // 👀 Liste des abonnés
    Route::get('/followers', [UserController::class, 'followers'])->name('users.followers');
});

/*
|--------------------------------------------------------------------------
| AUTHENTIFICATION
|--------------------------------------------------------------------------
*/

// 🔸 Routes d’authentification Breeze
require __DIR__ . '/auth.php';

// 🔸 ✅ Redéfinition forcée de la route /login (vue combinée)
Route::get('/login', function () {
    return auth()->check()
        ? redirect()->route('cards.index')
        : view('auth.login-register');
})->name('login');
