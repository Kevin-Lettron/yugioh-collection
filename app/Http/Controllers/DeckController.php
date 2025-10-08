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
     * Formulaire de création d’un deck (avec filtres GET persistants).
     */
    public function create(Request $request)
    {
        $user = Auth::user();

        // 1) Valider les filtres GET
        $validated = $request->validate([
            'type'   => ['nullable','string','max:100'],            // card_type
            'level'  => ['nullable','integer','min:0','max:12'],    // niveau EXACT
            'atk'    => ['nullable','integer','min:0','max:99999'], // min
            'def'    => ['nullable','integer','min:0','max:99999'], // min
            'rarity' => ['nullable','string','max:100'],
            'search' => ['nullable','string','max:100'],            // name / set_code / ucard_id
        ]);

        // 2) Types disponibles dans la collection (pour le select dynamique)
        $availableTypes = $user->cards()
            ->select('card_type')
            ->whereNotNull('card_type')
            ->distinct()
            ->orderBy('card_type')
            ->pluck('card_type');

        // 3) Requête des cartes de la collection + filtres
        $cards = $user->cards()
            ->select(['id','name','card_type','level','atk','def','rarity','nm_exemplaire','set_code','ucard_id'])
            ->when($validated['type'] ?? null,   fn($q, $v) => $q->where('card_type', $v))
            ->when($validated['level'] ?? null,  fn($q, $v) => $q->where('level', $v))
            ->when($validated['atk'] ?? null,    fn($q, $v) => $q->where('atk', '>=', $v))
            ->when($validated['def'] ?? null,    fn($q, $v) => $q->where('def', '>=', $v))
            ->when($validated['rarity'] ?? null, fn($q, $v) => $q->where('rarity', $v))
            ->when($validated['search'] ?? null, function ($q, $term) {
                $like = '%'.trim($term).'%';
                $q->where(function ($qq) use ($like) {
                    $qq->where('name', 'LIKE', $like)
                       ->orWhere('set_code', 'LIKE', $like)
                       ->orWhere('ucard_id', 'LIKE', $like);
                });
            })
            ->orderBy('name')
            ->get()
            ->map(function ($card) use ($user) {
                $card->available_quantity = $card->availableQuantityForUser($user->id);
                $card->selected_quantity  = 0;
                return $card;
            });

        return view('decks.create', [
            'cards'          => $cards,           // déjà filtrées côté serveur
            'availableTypes' => $availableTypes,  // pour le select "Type"
            'filters'        => $validated,       // si tu veux t'en servir dans la vue
        ]);
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

        $totalCards = 0;
        foreach ($validated['cards'] as $cardId) {
            $qty = (int) ($validated['quantities'][$cardId] ?? 0);
            $totalCards += $qty;
        }

        if ($totalCards < 40 || $totalCards > 60) {
            return back()
                ->withErrors(['cards' => 'Le deck doit contenir entre 40 et 60 cartes au total.'])
                ->withInput();
        }

        $deck = $user->decks()->create([
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        foreach ($validated['cards'] as $cardId) {
            $card = Card::find($cardId);
            if (!$card) continue;

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
     * Formulaire d’édition d’un deck (avec filtres GET persistants).
     */
    public function edit(Request $request, Deck $deck)
    {
        abort_if($deck->user_id !== Auth::id(), 403);
        $user = Auth::user();

        // 1) Valider les filtres GET (mêmes règles que deck.show)
        $validated = $request->validate([
            'type'   => ['nullable','string','max:100'],           // card_type
            'level'  => ['nullable','integer','min:0','max:12'],   // niveau EXACT
            'atk'    => ['nullable','integer','min:0','max:99999'],// min
            'def'    => ['nullable','integer','min:0','max:99999'],// min
            'rarity' => ['nullable','string','max:100'],
            'search' => ['nullable','string','max:100'],           // name / set_code / ucard_id
        ]);

        // 2) Types disponibles dans la collection du user (pour le select)
        $availableTypes = $user->cards()
            ->select('card_type')
            ->whereNotNull('card_type')
            ->distinct()
            ->orderBy('card_type')
            ->pluck('card_type');

        // 3) Quantités déjà dans le deck (pour initialiser selected_quantity)
        $deck->load(['cards' => function ($q) {
            $q->select('cards.id', 'name');
        }]);
        $deckCardQuantities = $deck->cards->pluck('pivot.quantity', 'id'); // [card_id => qty]

        // 4) Requête des cartes de la collection + filtres
        $cards = $user->cards()
            ->select(['id','name','card_type','level','atk','def','rarity','nm_exemplaire','set_code','ucard_id'])
            ->when($validated['type'] ?? null,   fn($q, $v) => $q->where('card_type', $v))
            ->when($validated['level'] ?? null,  fn($q, $v) => $q->where('level', $v))
            ->when($validated['atk'] ?? null,    fn($q, $v) => $q->where('atk', '>=', $v))
            ->when($validated['def'] ?? null,    fn($q, $v) => $q->where('def', '>=', $v))
            ->when($validated['rarity'] ?? null, fn($q, $v) => $q->where('rarity', $v))
            ->when($validated['search'] ?? null, function ($q, $term) {
                $like = '%'.trim($term).'%';
                $q->where(function ($qq) use ($like) {
                    $qq->where('name', 'LIKE', $like)
                       ->orWhere('set_code', 'LIKE', $like)
                       ->orWhere('ucard_id', 'LIKE', $like);
                });
            })
            ->orderBy('name')
            ->get()
            ->map(function ($card) use ($user, $deck, $deckCardQuantities) {
                // Quantité déjà utilisée dans ce deck
                $usedInDeck = (int) ($deckCardQuantities[$card->id] ?? 0);
                // Quantité dispo = méthode maison qui tient compte du deck courant
                $available = $card->availableQuantityForUser($user->id, $deck->id);

                $card->available_quantity = $available;
                $card->selected_quantity  = $usedInDeck;
                return $card;
            });

        // 5) Ancien format (compat) : couple [card_id => quantity] du deck
        $deckCards = $deck->cards()
            ->select('card_id', 'quantity')
            ->get();

        return view('decks.edit', [
            'deck'           => $deck,
            'cards'          => $cards,           // déjà filtrées côté serveur
            'deckCards'      => $deckCards,
            'availableTypes' => $availableTypes,
            'filters'        => $validated,
        ]);
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

        $totalCards = 0;
        foreach ($validated['cards'] as $cardId) {
            $qty = (int) ($validated['quantities'][$cardId] ?? 0);
            $totalCards += $qty;
        }

        if ($totalCards < 40 || $totalCards > 60) {
            return back()
                ->withErrors(['cards' => 'Le deck doit contenir entre 40 et 60 cartes au total.'])
                ->withInput();
        }

        $syncData = [];
        foreach ($validated['cards'] as $cardId) {
            $card = Card::find($cardId);
            if (!$card) continue;

            $available  = $card->availableQuantityForUser($user->id, $deck->id);
            $qty        = min(3, $validated['quantities'][$cardId] ?? 1);
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
     * Affichage d’un deck avec filtres.
     */
    public function show(Request $request, Deck $deck)
    {
        $authUser = Auth::user();

        $isOwner = $deck->user_id === $authUser->id;
        $isFollowing = $authUser->following()
            ->where('followed_id', $deck->user_id)
            ->exists();

        if (!$isOwner && !$isFollowing) {
            abort(403, 'Vous n’êtes pas autorisé à consulter ce deck.');
        }

        // ✅ Liste des types réellement présents dans le deck (valeurs sûres)
        $typeOptions = $deck->cards()
            ->select('cards.card_type')
            ->distinct()
            ->orderBy('cards.card_type')
            ->pluck('cards.card_type');

        // ✅ Requête avec filtres – niveau exact (=)
        $cards = $deck->cards()
            ->withPivot('quantity')
            ->when($request->filled('type'), fn($q) => $q->where('cards.card_type', $request->type))
            ->when($request->filled('level'), fn($q) => $q->where('cards.level', (int) $request->level))
            ->when($request->filled('atk'), fn($q) => $q->where('cards.atk', '>=', (int) $request->atk))
            ->when($request->filled('def'), fn($q) => $q->where('cards.def', '>=', (int) $request->def))
            ->when($request->filled('rarity'), fn($q) => $q->where('cards.rarity', $request->rarity))
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->search;
                $q->where(function ($sub) use ($s) {
                    $sub->where('cards.name', 'like', "%{$s}%")
                        ->orWhere('cards.ucard_id', 'like', "%{$s}%")
                        ->orWhere('cards.set_code', 'like', "%{$s}%");
                });
            })
            ->orderBy('cards.name')
            ->get();

        $readOnly = !$isOwner;

        return view('decks.show', compact('deck', 'cards', 'readOnly', 'typeOptions'));
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
