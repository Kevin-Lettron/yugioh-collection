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
     * Enregistre une carte via son identifiant complet (ex : 46986414-LDK2-FRY10)
     */
    public function store(Request $request)
    {
        $request->validate([
            'codes' => 'required|string|max:50',
        ]);

        $code = strtoupper(trim($request->input('codes')));
        [$card_id, $set_code] = array_pad(explode('-', $code, 2), 2, null);

        if (!is_numeric($card_id)) {
            Log::error("Le code {$code} est invalide : ID non numérique.");
            return back()->withErrors(['codes' => 'Le code doit être du format 46986414-LDK2-FRY10 (ID-SETCODE).']);
        }

        $result = $this->findCardById($card_id, $set_code);

        if (isset($result['error'])) {
            Log::error("Erreur de recherche de carte : " . $result['error']);
            return back()->withErrors(['codes' => $result['error']]);
        }

        $cardData = $result['data'];
        $set = $result['set'];

        try {
            Log::info("Enregistrement de la carte avec code : {$code} - Nom : {$cardData['name']}");

            $level = $cardData['level'] ?? null;

            // ✅ Vérifie si la carte existe déjà pour CE user
            $card = Card::where('user_id', Auth::id())
                        ->where('ucard_id', $card_id)
                        ->where('set_code', $set_code)
                        ->first();

            if ($card) {
                $card->increment('nm_exemplaire');
            } else {
                $card = Card::create([
                    'ucard_id'       => $card_id,
                    'set_code'       => $set_code,
                    'code'           => "{$card_id}-{$set_code}",
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
                ->with('success', "✅ Carte « {$card->name} » ajoutée avec succès !");
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'enregistrement de la carte : " . $e->getMessage());
            return back()->withErrors(['codes' => "Erreur d'enregistrement : {$e->getMessage()}"]);
        }
    }

    /**
     * API interne pour la recherche AJAX d’une carte.
     */
    public function apiFindCard($code)
    {
        [$id, $setCode] = array_pad(explode('-', strtoupper(trim($code)), 2), 2, null);
        Log::info("Recherche de la carte via API", ['id' => $id, 'setCode' => $setCode]);

        $result = $this->findCardById($id, $setCode);

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
        ]);
    }

    /**
     * Recherche d’une carte par ID numérique et set code.
     */
    private function findCardById(string $id, ?string $setCode): array
    {
        try {
            $cacheKey = "ygoprodeck_card_{$id}_{$setCode}";
            $cardData = Cache::remember($cacheKey, 86400, function () use ($id, $setCode) {
                Log::info("Appel API pour obtenir les données de la carte", ['id' => $id, 'setCode' => $setCode]);
                $response = Http::get("https://db.ygoprodeck.com/api/v7/cardinfo.php", ['id' => $id]);

                if ($response->failed() || !isset($response['data'][0])) {
                    Log::error("L'API a échoué ou aucune carte trouvée", ['response' => $response->json()]);
                    return null;
                }

                return $response['data'][0];
            });

            if (!$cardData) {
                return ['error' => "Aucune carte trouvée pour l’ID {$id}."];
            }

            $set = null;
            if ($setCode && isset($cardData['card_sets'])) {
                foreach ($cardData['card_sets'] as $s) {
                    if (strtoupper($s['set_code']) === strtoupper($setCode)) {
                        $set = $s;
                        break;
                    }
                }
            }

            if (!$set && isset($cardData['card_sets'][0])) {
                $set = $cardData['card_sets'][0];
            }

            return ['data' => $cardData, 'set' => $set ?? ['set_code' => 'UNKNOWN']];
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
