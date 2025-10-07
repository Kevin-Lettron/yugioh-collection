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
     * Affiche la collection de l'utilisateur connecté.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $type = $request->query('type');
        $level = $request->query('level');
        $atk = $request->query('atk');
        $def = $request->query('def');
        $rarity = $request->query('rarity');

        $cards = Auth::user()->cards()
            ->when($search, function ($query) use ($search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('ucard_id', 'like', "%{$search}%")
                      ->orWhere('set_code', 'like', "%{$search}%");
                });
            })
            ->when($type, fn($query) => $query->where('card_type', $type))
            ->when($level, fn($query) => $query->where('level', $level))
            ->when($atk, fn($query) => $query->where('atk', '>=', $atk))
            ->when($def, fn($query) => $query->where('def', '>=', $def))
            ->when($rarity, fn($query) => $query->where('rarity', $rarity))
            ->latest()
            ->get();

        return view('cards.index', compact('cards'));
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
                // ✅ Incrémentation correcte pour l'utilisateur connecté
                $card->increment('nm_exemplaire');
            } else {
                // ✅ Création si nouvelle carte
                $card = Card::create([
                    'ucard_id' => $card_id,
                    'set_code' => $set_code,
                    'code' => "{$card_id}-{$set_code}",
                    'name' => $cardData['name'] ?? 'Inconnue',
                    'card_type' => $cardData['type'] ?? 'Inconnu',
                    'description' => $cardData['desc'] ?? '',
                    'atk' => $cardData['atk'] ?? null,
                    'def' => $cardData['def'] ?? null,
                    'rarity' => $set['set_rarity'] ?? null,
                    'price' => $set['set_price'] ?? null,
                    'level' => $level,
                    'user_id' => Auth::id(),
                    'nm_exemplaire' => 1,
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
            'name' => $card['name'] ?? 'Inconnue',
            'type' => $card['type'] ?? 'Inconnu',
            'atk' => $card['atk'] ?? null,
            'def' => $card['def'] ?? null,
            'rarity' => $set['set_rarity'] ?? null,
            'price' => $set['set_price'] ?? null,
            'image' => $card['card_images'][0]['image_url'] ?? null,
            'description' => $card['desc'] ?? null,
            'level' => $card['level'] ?? null,
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
            'price' => 'nullable|numeric',
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
