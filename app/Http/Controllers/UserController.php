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
     * 🧩 Affiche la collection publique d’un utilisateur (avec filtres et pagination).
     */
    public function collection(Request $request, User $user)
    {
        // ✅ Valider les filtres GET (mêmes règles que deck.show)
        $validated = $request->validate([
            'type'   => ['nullable', 'string', 'max:100'],          // card_type
            'level'  => ['nullable', 'integer', 'min:0', 'max:12'], // niveau EXACT
            'atk'    => ['nullable', 'integer', 'min:0', 'max:99999'], // min
            'def'    => ['nullable', 'integer', 'min:0', 'max:99999'], // min
            'rarity' => ['nullable', 'string', 'max:100'],
            'search' => ['nullable', 'string', 'max:100'],          // name / set_code / ucard_id
        ]);

        // 🔁 Types réellement présents dans la collection (pour le select dynamique)
        $availableTypes = $user->cards()
            ->select('card_type')
            ->whereNotNull('card_type')
            ->distinct()
            ->orderBy('card_type')
            ->pluck('card_type');

        // 🔎 Requête filtrée
        $cardsQuery = $user->cards()
            ->select('id', 'name', 'card_type', 'level', 'atk', 'def', 'rarity', 'nm_exemplaire', 'set_code', 'ucard_id')
            ->when($validated['type'] ?? null,   fn($q, $v) => $q->where('card_type', $v))
            ->when($validated['level'] ?? null,  fn($q, $v) => $q->where('level', $v))
            ->when($validated['atk'] ?? null,    fn($q, $v) => $q->where('atk', '>=', $v))
            ->when($validated['def'] ?? null,    fn($q, $v) => $q->where('def', '>=', $v))
            ->when($validated['rarity'] ?? null, fn($q, $v) => $q->where('rarity', $v))
            ->when($validated['search'] ?? null, function ($q, $term) {
                $like = '%' . trim($term) . '%';
                $q->where(function ($qq) use ($like) {
                    $qq->where('name', 'LIKE', $like)
                       ->orWhere('set_code', 'LIKE', $like)
                       ->orWhere('ucard_id', 'LIKE', $like);
                });
            })
            ->orderBy('name');

        // 📊 Compteur global de cartes de la collection (optionnel dans l’entête)
        $collectionCount = $user->cards()->count();

        // 📄 Pagination + persistance des filtres
        $cards = $cardsQuery->paginate(20)->appends($validated);

        return view('users.collection', [
            'user'            => $user,
            'cards'           => $cards,            // LengthAwarePaginator -> firstItem/lastItem/total OK
            'availableTypes'  => $availableTypes,
            'collectionCount' => $collectionCount,
            'filters'         => $validated,
        ]);
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

        // 🔵 On efface le flag "nouveau follower" dès l’ouverture
        if ($user->has_new_follower) {
            $user->update(['has_new_follower' => false]);
        }

        $followers = $user->followers()->orderBy('name')->get();

        return view('users.followers', compact('followers'));
    }
}
