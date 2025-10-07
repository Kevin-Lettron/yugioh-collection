<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Affiche la page de recherche des utilisateurs.
     */
    public function index(Request $request)
{
    $query = $request->input('query');

    $users = User::where('id', '!=', Auth::id())
        ->when($query, function ($q) use ($query) {
            $q->where(function ($subQuery) use ($query) {
                $subQuery->where('name', 'like', "%{$query}%")
                         ->orWhere('email', 'like', "%{$query}%");
            });
        })
        ->orderBy('name')
        ->paginate(10) // ✅ OBLIGATOIRE
        ->withQueryString();

    return view('users.index', compact('users', 'query'));
}

    /**
     * Suivre un utilisateur.
     */
    public function follow(User $user)
    {
        $authUser = Auth::user();

        // Empêche de se suivre soi-même
        if ($authUser->id === $user->id) {
            return back()->with('error', "Vous ne pouvez pas vous suivre vous-même.");
        }

        // Évite les doublons
        if (!$authUser->isFollowing($user)) {
            $authUser->following()->attach($user->id);
        }

        return back()->with('success', "Vous suivez désormais {$user->name} !");
    }

    /**
     * Se désabonner d’un utilisateur.
     */
    public function unfollow(User $user)
    {
        $authUser = Auth::user();

        if ($authUser->isFollowing($user)) {
            $authUser->following()->detach($user->id);
        }

        return back()->with('success', "Vous ne suivez plus {$user->name}.");
    }

    /**
     * Affiche le profil public d’un utilisateur avec ses decks et cartes.
     */
    public function show(User $user)
{
    // Empêche un accès non autorisé au profil (facultatif)
    if (!Auth::check()) {
        return redirect()->route('login');
    }

    // Récupération des decks et des cartes de l'utilisateur visité
    $decks = $user->decks()->latest()->get();
    $cards = $user->cards()->latest()->get();

    // Vérifie si l’utilisateur connecté le suit déjà
    $isFollowing = Auth::user()->isFollowing($user);

    return view('users.show', compact('user', 'decks', 'cards', 'isFollowing'));
}
public function collection(User $user)
{
    // On récupère les cartes du user avec le champ nm_exemplaire
    $cards = $user->cards()
        ->select('id', 'name', 'card_type', 'level', 'atk', 'def', 'rarity', 'nm_exemplaire') // 👈 AJOUT ICI
        ->latest()
        ->get();

    return view('users.collection', compact('user', 'cards'));
}

public function decks(User $user)
{
    $decks = $user->decks()->latest()->get();
    $isFollowing = Auth::user()->isFollowing($user);

    return view('users.decks', compact('user', 'decks', 'isFollowing'));
}
    /**
     * Affiche les utilisateurs suivis par l'utilisateur connecté.
     */
    public function following()
    {
        $user = Auth::user();
        $following = $user->following()->orderBy('name')->get();

        return view('users.following', compact('following'));
    }

    /**
     * Affiche les abonnés (ceux qui me suivent).
     */
    public function followers()
    {
        $user = Auth::user();
        $followers = $user->followers()->orderBy('name')->get();

        return view('users.followers', compact('followers'));
    }
}
