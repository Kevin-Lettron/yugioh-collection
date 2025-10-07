<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * 🔎 Affiche la page de recherche des utilisateurs.
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
            ->paginate(10)
            ->withQueryString();

        return view('users.index', compact('users', 'query'));
    }

    /**
     * ➕ Suivre un utilisateur.
     */
    public function follow(User $user)
    {
        $follower = Auth::user();

        if ($follower->id === $user->id) {
            return back()->with('error', 'Tu ne peux pas te suivre toi-même.');
        }

        // ✅ Empêche les doublons
        $follower->following()->syncWithoutDetaching([$user->id]);

        // 🔴 Active la notification pour l’utilisateur suivi
        if (!$user->has_new_follower) {
            $user->update(['has_new_follower' => true]);
        }

        return back()->with('success', "Tu suis maintenant {$user->name} !");
    }

    /**
     * ➖ Se désabonner d’un utilisateur.
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
     * 👤 Affiche le profil public d’un utilisateur.
     */
    public function show(User $user)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $decks = $user->decks()->latest()->take(5)->get();
        $cards = $user->cards()->latest()->take(10)->get();
        $isFollowing = Auth::user()->isFollowing($user);

        return view('users.show', compact('user', 'decks', 'cards', 'isFollowing'));
    }

    /**
     * 🧩 Affiche la collection publique d’un utilisateur.
     */
    public function collection(User $user)
    {
        $cards = $user->cards()
            ->select('id', 'name', 'card_type', 'level', 'atk', 'def', 'rarity', 'nm_exemplaire')
            ->latest()
            ->paginate(10); // 🔹 Pagination dynamique

        return view('users.collection', compact('user', 'cards'));
    }

    /**
     * 🧱 Affiche les decks publics d’un utilisateur.
     */
    public function decks(User $user)
    {
        $decks = $user->decks()->latest()->paginate(10);
        $isFollowing = Auth::user()->isFollowing($user);

        return view('users.decks', compact('user', 'decks', 'isFollowing'));
    }

    /**
     * 🤝 Affiche les utilisateurs suivis.
     */
    public function following()
    {
        $user = Auth::user();
        $following = $user->following()->orderBy('name')->get();

        return view('users.following', compact('following'));
    }

    /**
     * 👀 Affiche les abonnés (followers).
     */
    public function followers()
    {
        $user = Auth::user();

        // 🔵 Supprime le signal de nouvelle notification dès que l’utilisateur ouvre la page
        if ($user->has_new_follower) {
            $user->update(['has_new_follower' => false]);
        }

        $followers = $user->followers()->orderBy('name')->get();

        return view('users.followers', compact('followers'));
    }
}
