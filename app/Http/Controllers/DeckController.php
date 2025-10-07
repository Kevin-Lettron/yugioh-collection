<?php

namespace App\Http\Controllers;

use App\Models\Deck;
use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeckController extends Controller
{
    /**
     * Liste des decks de l'utilisateur connecté.
     */
    public function index(Request $request)
    {
        $query = Auth::user()->decks();

        if ($search = $request->query('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $decks = $query->latest()->get();

        return view('decks.index', compact('decks'));
    }

    /**
     * Formulaire de création d’un deck.
     */
    public function create()
    {
        $user = Auth::user();

        // 🧮 Calcule pour chaque carte : quantité totale - cartes utilisées dans d’autres decks
        $cards = $user->cards()->get()->map(function ($card) use ($user) {
            $card->available_quantity = $card->availableQuantityForUser($user->id);
            $card->selected_quantity = 0;
            return $card;
        });

        return view('decks.create', compact('cards'));
    }

    /**
     * Enregistrement d’un nouveau deck.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'cards'       => 'required|array',
            'cards.*'     => 'exists:cards,id',
            'quantities'  => 'required|array',
        ]);

        $user = Auth::user();

        // 🧮 Calcul du total réel de cartes (somme des quantités)
        $totalCards = 0;
        foreach ($validated['cards'] as $cardId) {
            $qty = isset($validated['quantities'][$cardId]) ? (int)$validated['quantities'][$cardId] : 0;
            $totalCards += $qty;
        }

        // ✅ Validation du total
        if ($totalCards < 40 || $totalCards > 60) {
            return back()
                ->withErrors(['cards' => 'Le deck doit contenir entre 40 et 60 cartes au total.'])
                ->withInput();
        }

        // ✅ Création du deck
        $deck = $user->decks()->create([
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        // ✅ Attacher les cartes avec leur quantité
        foreach ($validated['cards'] as $cardId) {
            $card = Card::find($cardId);
            if (!$card) continue;

            // Quantité réellement disponible (total - déjà utilisée ailleurs)
            $available = $card->availableQuantityForUser($user->id);
            $qty = min(3, $validated['quantities'][$cardId] ?? 1);

            if ($qty > $available) {
                return back()->withErrors([
                    'cards' => "La carte {$card->name} dépasse la quantité disponible ({$available} max).",
                ])->withInput();
            }

            $deck->cards()->attach($cardId, ['quantity' => $qty]);
        }

        return redirect()->route('decks.index')->with('success', '✅ Deck créé avec succès !');
    }

    /**
     * Formulaire d’édition d’un deck.
     */
    public function edit(Deck $deck)
    {
        abort_if($deck->user_id !== Auth::id(), 403);

        $user = Auth::user();

        // 🧮 Récupère toutes les cartes avec quantité disponible
        $cards = $user->cards()->get()->map(function ($card) use ($deck, $user) {
            $usedInDeck = $deck->cards()->where('card_id', $card->id)->first()?->pivot->quantity ?? 0;
            $available = $card->availableQuantityForUser($user->id, $deck->id);
            $card->available_quantity = $available;
            $card->selected_quantity  = $usedInDeck;
            return $card;
        });

        // 🧩 Récupère les cartes déjà dans le deck (pivot)
        $deckCards = $deck->cards()
            ->select('card_id', 'quantity')
            ->get();

        // 🔹 Passe tout à la vue
        return view('decks.edit', compact('deck', 'cards', 'deckCards'));
    }

    /**
     * Mise à jour d’un deck existant.
     */
    public function update(Request $request, Deck $deck)
    {
        abort_if($deck->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'cards'      => 'required|array',
            'cards.*'    => 'exists:cards,id',
            'quantities' => 'required|array',
        ]);

        $user = Auth::user();

        // 🧮 Calcul du total réel
        $totalCards = 0;
        foreach ($validated['cards'] as $cardId) {
            $qty = isset($validated['quantities'][$cardId]) ? (int)$validated['quantities'][$cardId] : 0;
            $totalCards += $qty;
        }

        // ✅ Validation du total
        if ($totalCards < 40 || $totalCards > 60) {
            return back()
                ->withErrors(['cards' => 'Le deck doit contenir entre 40 et 60 cartes au total.'])
                ->withInput();
        }

        // ✅ Synchronisation des cartes
        $syncData = [];
        foreach ($validated['cards'] as $cardId) {
            $card = Card::find($cardId);
            if (!$card) continue;

            $available = $card->availableQuantityForUser($user->id, $deck->id);
            $qty = min(3, $validated['quantities'][$cardId] ?? 1);
            $usedInDeck = $deck->cards()->where('card_id', $cardId)->first()?->pivot->quantity ?? 0;

            if ($qty > $available + $usedInDeck) {
                return back()->withErrors([
                    'cards' => "La carte {$card->name} dépasse la quantité disponible ({$available} max).",
                ])->withInput();
            }

            $syncData[$cardId] = ['quantity' => $qty];
        }

        $deck->update([
            'name'        => $request->name ?? $deck->name,
            'description' => $request->description ?? $deck->description,
        ]);

        $deck->cards()->sync($syncData);

        return redirect()->route('decks.index')->with('success', '✅ Deck mis à jour avec succès !');
    }

    /**
     * Affichage d’un deck.
     */
public function show(Deck $deck)
{
    $authUser = Auth::user();

    // 🔹 Vérifie si l’utilisateur connecté est le propriétaire
    $isOwner = $deck->user_id === $authUser->id;

    // 🔹 Vérifie s’il suit le propriétaire du deck
    $isFollowing = $authUser->following()
        ->where('followed_id', $deck->user_id)
        ->exists();

    // 🚫 Si ce n’est ni le propriétaire ni un suiveur → accès refusé
    if (!$isOwner && !$isFollowing) {
        abort(403, 'Vous n’êtes pas autorisé à consulter ce deck.');
    }

    // ✅ Récupère les cartes liées au deck (avec quantité)
    $cards = $deck->cards()->withPivot('quantity')->get();

    // 🔹 Passe un flag à la vue pour gérer lecture seule
    $readOnly = !$isOwner;

    return view('decks.show', compact('deck', 'cards', 'readOnly'));
}

    /**
     * Suppression d’un deck.
     */
    public function destroy(Deck $deck)
    {
        abort_if($deck->user_id !== Auth::id(), 403);

        $deck->cards()->detach();
        $deck->delete();

        return redirect()->route('decks.index')->with('success', '🗑️ Deck supprimé avec succès !');
    }
}
