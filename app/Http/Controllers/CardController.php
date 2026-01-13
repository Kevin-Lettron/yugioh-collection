<?php

namespace App\Http\Controllers;

use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CardController extends Controller
{
    /**
     * Affiche la collection de l'utilisateur connecté avec filtres + pagination.
     */
    public function index(Request $request)
    {
        // Validation douce des filtres (mêmes règles que deck.show)
        $validated = $request->validate([
            'type'   => ['nullable','string','max:100'],          // card_type
            'level'  => ['nullable','integer','min:0','max:12'],  // niveau EXACT
            'atk'    => ['nullable','integer','min:0','max:99999'], // min
            'def'    => ['nullable','integer','min:0','max:99999'], // min
            'rarity' => ['nullable','string','max:100'],
            'search' => ['nullable','string','max:100'],          // name / ucard_id / set_code
        ]);

        $user = Auth::user();
        abort_unless($user, 403);

        // Types disponibles dans la collection (pour le select dynamique)
        $availableTypes = $user->cards()
            ->select('card_type')
            ->whereNotNull('card_type')
            ->distinct()
            ->orderBy('card_type')
            ->pluck('card_type');

        // Requête filtrée
        $cardsQuery = $user->cards()
            ->select('id','ucard_id','set_code','name','card_type','level','atk','def','rarity','price','nm_exemplaire')
            ->when($validated['search'] ?? null, function ($query, $search) {
                $like = '%'.trim($search).'%';
                $query->where(function ($q) use ($like) {
                    $q->where('name', 'like', $like)
                      ->orWhere('ucard_id', 'like', $like)
                      ->orWhere('set_code', 'like', $like);
                });
            })
            ->when($validated['type'] ?? null,   fn($q, $v) => $q->where('card_type', $v))
            ->when($validated['level'] ?? null,  fn($q, $v) => $q->where('level', $v))
            ->when($validated['atk'] ?? null,    fn($q, $v) => $q->where('atk', '>=', $v))
            ->when($validated['def'] ?? null,    fn($q, $v) => $q->where('def', '>=', $v))
            ->when($validated['rarity'] ?? null, fn($q, $v) => $q->where('rarity', $v))
            ->orderBy('name');

        // Pagination + persistance des filtres
        $cards = $cardsQuery->paginate(20)->appends($validated);

        return view('cards.index', [
            'cards'          => $cards,
            'availableTypes' => $availableTypes,
            'filters'        => $validated,
        ]);
    }

    /**
     * Formulaire d’ajout d’une carte.
     */
    public function create()
    {
        return view('cards.create');
    }

    /**
     * Enregistre une carte via son code d'édition (ex : LDK2-FRY10, LOB-001)
     */
    public function store(Request $request)
    {
        $request->validate([
            'codes' => 'required|string|max:50',
        ]);

        $setCode = strtoupper(trim($request->input('codes')));

        $result = $this->findCardBySetCode($setCode);

        if (isset($result['error'])) {
            Log::error("Erreur de recherche de carte : " . $result['error']);
            return back()->withErrors(['codes' => $result['error']]);
        }

        $cardData = $result['data'];
        $set = $result['set'];
        $cardId = $result['card_id'];

        try {
            Log::info("Enregistrement de la carte avec set code : {$setCode} - Nom : {$cardData['name']}");

            $level = $cardData['level'] ?? null;

            // Vérifie si la carte existe déjà pour CE user (même carte + même édition)
            $card = Card::where('user_id', Auth::id())
                        ->where('ucard_id', $cardId)
                        ->where('set_code', $setCode)
                        ->first();

            if ($card) {
                $card->increment('nm_exemplaire');
            } else {
                $card = Card::create([
                    'ucard_id'       => $cardId,
                    'set_code'       => $setCode,
                    'code'           => "{$cardId}-{$setCode}",
                    'name'           => $cardData['name'] ?? 'Inconnue',
                    'card_type'      => $cardData['type'] ?? 'Inconnu',
                    'description'    => $cardData['desc'] ?? '',
                    'atk'            => $cardData['atk'] ?? null,
                    'def'            => $cardData['def'] ?? null,
                    'rarity'         => $set['set_rarity'] ?? null,
                    'price'          => $set['set_price'] ?? null,
                    'level'          => $level,
                    'user_id'        => Auth::id(),
                    'nm_exemplaire'  => 1,
                ]);
            }

            Log::info("Carte enregistrée avec succès : {$card->name}");

            return redirect()
                ->route('cards.index')
                ->with('success', "Carte « {$card->name} » ajoutée avec succès !");
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'enregistrement de la carte : " . $e->getMessage());
            return back()->withErrors(['codes' => "Erreur d'enregistrement : {$e->getMessage()}"]);
        }
    }

    /**
     * API interne pour la recherche AJAX d'une carte via son set code (ex: LDK2-FRY10).
     */
    public function apiFindCard($code)
    {
        $setCode = strtoupper(trim($code));
        Log::info("Recherche de la carte via API", ['setCode' => $setCode]);

        $result = $this->findCardBySetCode($setCode);

        if (isset($result['error'])) {
            Log::error("Erreur lors de la recherche de la carte", ['error' => $result['error']]);
            return response()->json(['error' => $result['error']], 404);
        }

        $card = $result['data'];
        $set = $result['set'];

        return response()->json([
            'name'        => $card['name'] ?? 'Inconnue',
            'type'        => $card['type'] ?? 'Inconnu',
            'atk'         => $card['atk'] ?? null,
            'def'         => $card['def'] ?? null,
            'rarity'      => $set['set_rarity'] ?? null,
            'price'       => $set['set_price'] ?? null,
            'image'       => $card['card_images'][0]['image_url'] ?? null,
            'description' => $card['desc'] ?? null,
            'level'       => $card['level'] ?? null,
            'set_name'    => $set['set_name'] ?? null,
        ]);
    }

    /**
     * Recherche d'une carte par set code uniquement (ex: LDK2-FRY10, LOB-001).
     * Utilise l'API cardsetsinfo.php pour obtenir l'ID, puis cardinfo.php pour les détails.
     */
    private function findCardBySetCode(string $setCode): array
    {
        try {
            $setCode = strtoupper(trim($setCode));
            $cacheKey = "ygoprodeck_setcode_{$setCode}";

            $result = Cache::remember($cacheKey, 86400, function () use ($setCode) {
                Log::info("Appel API cardsetsinfo pour obtenir l'ID de la carte", ['setCode' => $setCode]);

                // Étape 1: Obtenir l'ID de la carte via le set code
                $setInfoResponse = Http::get("https://db.ygoprodeck.com/api/v7/cardsetsinfo.php", ['setcode' => $setCode]);

                if ($setInfoResponse->failed()) {
                    Log::error("L'API cardsetsinfo a échoué", ['response' => $setInfoResponse->json()]);
                    return null;
                }

                $setInfo = $setInfoResponse->json();

                if (!isset($setInfo['id'])) {
                    Log::error("Aucune carte trouvée pour ce set code", ['setCode' => $setCode]);
                    return null;
                }

                $cardId = $setInfo['id'];
                Log::info("ID de carte trouvé", ['cardId' => $cardId, 'cardName' => $setInfo['name'] ?? 'Unknown']);

                // Étape 2: Obtenir les détails complets de la carte
                $cardInfoResponse = Http::get("https://db.ygoprodeck.com/api/v7/cardinfo.php", ['id' => $cardId]);

                if ($cardInfoResponse->failed() || !isset($cardInfoResponse['data'][0])) {
                    Log::error("L'API cardinfo a échoué", ['response' => $cardInfoResponse->json()]);
                    return null;
                }

                return [
                    'cardData' => $cardInfoResponse['data'][0],
                    'setInfo' => $setInfo
                ];
            });

            if (!$result) {
                return ['error' => "Aucune carte trouvée pour le code « {$setCode} ». Vérifiez le format (ex: LDK2-FRY10, LOB-001)."];
            }

            $cardData = $result['cardData'];
            $setInfo = $result['setInfo'];

            // Construire les infos du set
            $set = [
                'set_code' => $setInfo['set_code'] ?? $setCode,
                'set_name' => $setInfo['set_name'] ?? null,
                'set_rarity' => $setInfo['set_rarity'] ?? null,
                'set_price' => $setInfo['set_price'] ?? null,
            ];

            return [
                'data' => $cardData,
                'set' => $set,
                'card_id' => $setInfo['id']
            ];
        } catch (\Exception $e) {
            Log::error("Erreur lors de la recherche de la carte", ['error' => $e->getMessage()]);
            return ['error' => 'Erreur lors de la recherche de la carte : ' . $e->getMessage()];
        }
    }

    /**
     * Formulaire d’édition.
     */
    public function edit(Card $card)
    {
        return view('cards.edit', compact('card'));
    }

    /**
     * Mise à jour d’une carte.
     */
    public function update(Request $request, Card $card)
    {
        $validated = $request->validate([
            'price'         => 'nullable|numeric',
            'nm_exemplaire' => 'nullable|integer|min:1',
        ]);

        $card->update($validated);

        return redirect()->route('cards.index')->with('success', 'Carte mise à jour avec succès !');
    }

    /**
     * Suppression d’une carte.
     */
    public function destroy(Card $card)
    {
        $card->delete();
        return redirect()
            ->route('cards.index')
            ->with('success', 'Carte supprimée avec succès !');
    }
}
