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
    $search = $request->query('search'); // Récupérer le paramètre de recherche

    $type = $request->query('type'); // Type de carte
    $level = $request->query('level'); // Rang de monstre
    $atk = $request->query('atk'); // ATK
    $def = $request->query('def'); // DEF
    $rarity = $request->query('rarity'); // Rareté

    // Recherche filtrée par nom, ID ou série
    $cards = Auth::user()->cards()
        ->when($search, function ($query) use ($search) {
            return $query->where('name', 'like', "%{$search}%")
                         ->orWhere('ucard_id', 'like', "%{$search}%")
                         ->orWhere('set_code', 'like', "%{$search}%");
        })
        ->when($type, function ($query) use ($type) {
            return $query->where('card_type', $type);
        })
        ->when($level, function ($query) use ($level) {
            return $query->where('level', $level);
        })
        ->when($atk, function ($query) use ($atk) {
            return $query->where('atk', '>=', $atk);
        })
        ->when($def, function ($query) use ($def) {
            return $query->where('def', '>=', $def);
        })
        ->when($rarity, function ($query) use ($rarity) {
            return $query->where('rarity', $rarity);
        })
        ->latest() // Récupère les cartes les plus récentes
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
    // Validation de la saisie
    $request->validate([
        'codes' => 'required|string|max:50',
    ]);

    // Extraction du code ID et set_code
    $code = strtoupper(trim($request->input('codes')));
    [$card_id, $set_code] = array_pad(explode('-', $code, 2), 2, null);

    // Vérification de la validité de l'ID
    if (!is_numeric($card_id)) {
        Log::error("Le code {$code} est invalide : ID n'est pas numérique.");
        return back()->withErrors(['codes' => 'Le code doit être du format 46986414-LDK2-FRY10 (ID-SETCODE).']);
    }

    // Recherche la carte par ID
    $result = $this->findCardById($card_id, $set_code);

    if (isset($result['error'])) {
        Log::error("Erreur de recherche de carte : " . $result['error']);
        return back()->withErrors(['codes' => $result['error']]);
    }

    $cardData = $result['data'];
    $set = $result['set'];

    try {
        // Log avant l'enregistrement
        Log::info("Enregistrement de la carte avec code : {$code} - Nom : {$cardData['name']}");

        // Déterminer le level en fonction de l'entrée de l'utilisateur ou des données de l'API
        $level = $cardData['level'] ?? null;  // Le level est directement dans les données de l'API

        // Vérifier si la carte existe déjà dans la collection (basé sur ucard_id et set_code)
        $card = Card::where('ucard_id', $card_id)
                    ->where('set_code', $set_code)
                    ->first();

        if ($card) {
            // Si la carte existe, incrémenter le nombre d'exemplaires
            $card->increment('nm_exemplaire');
        } else {
            // Si la carte n'existe pas, créer un nouvel enregistrement
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
                'nm_exemplaire' => 1, // Premier exemplaire
            ]);
        }

        // Log succès d'enregistrement
        Log::info("Carte enregistrée avec succès : {$card->name}");

        return redirect()
            ->route('cards.index')
            ->with('success', "✅ Carte « {$card->name} » ajoutée avec succès !");
    } catch (\Exception $e) {
        // Capture l'exception d'enregistrement et log l'erreur
        Log::error("Erreur lors de l'enregistrement de la carte : " . $e->getMessage());
        return back()->withErrors(['codes' => "Erreur d'enregistrement : {$e->getMessage()}"]);
    }
}


    /**
     * API interne utilisée pour la recherche AJAX d’une carte via son code d’édition (ex: LDK2-FRY10)
     */
    public function apiFindCard($code)
    {
        [$id, $setCode] = array_pad(explode('-', strtoupper(trim($code)), 2), 2, null);

        // Log pour vérifier les valeurs d'ID et setCode avant de lancer la recherche
        Log::info("Recherche de la carte via API", ['id' => $id, 'setCode' => $setCode]);

        $result = $this->findCardById($id, $setCode);

        if (isset($result['error'])) {
            Log::error("Erreur lors de la recherche de la carte", ['error' => $result['error']]);
            return response()->json(['error' => $result['error']], 404);
        }

        $card = $result['data'];
        $set = $result['set'];

        // Log pour la réponse de l'API
        Log::info("Réponse de l'API pour la carte trouvée", ['card' => $card, 'set' => $set]);

        // Ajouter le level à la réponse de l'API
        return response()->json([
            'name' => $card['name'] ?? 'Inconnue',
            'type' => $card['type'] ?? 'Inconnu',
            'atk' => $card['atk'] ?? null,
            'def' => $card['def'] ?? null,
            'rarity' => $set['set_rarity'] ?? null,
            'price' => $set['set_price'] ?? null,
            'image' => $card['card_images'][0]['image_url'] ?? null,
            'description' => $card['desc'] ?? null,
            'level' => $card['level'] ?? null,  // Ajout du level dans la réponse API
        ]);
    }

    /**
     * Recherche d’une carte par ID numérique (ex : 46986414) et set code (ex : LDK2-FRY10)
     */
    private function findCardById(string $id, ?string $setCode): array
    {
        try {
            // Cache pour éviter de spammer l’API
            $cacheKey = "ygoprodeck_card_{$id}_{$setCode}"; // Utilisation du setCode pour plus de précision
            $cardData = Cache::remember($cacheKey, 86400, function () use ($id, $setCode) {
                // Log avant la requête API
                Log::info("Appel API pour obtenir les données de la carte", ['id' => $id, 'setCode' => $setCode]);

                // Recherche en utilisant seulement l'ID
                $response = Http::get("https://db.ygoprodeck.com/api/v7/cardinfo.php", [
                    'id' => $id,  // On utilise uniquement l'ID pour la recherche
                ]);

                // Log de la réponse de l'API
                Log::info("Réponse de l'API pour l'ID", ['response' => $response->json()]);

                if ($response->failed() || !isset($response['data'][0])) {
                    Log::error("L'API a échoué ou aucune carte trouvée", ['response' => $response->json()]);
                    return null;
                }

                return $response['data'][0];
            });

            if (!$cardData) {
                return ['error' => "Aucune carte trouvée pour l’ID {$id}."]; 
            }

            // Recherche du bon set dans card_sets
            $set = null;
            if ($setCode && isset($cardData['card_sets'])) {
                foreach ($cardData['card_sets'] as $s) {
                    if (strtoupper($s['set_code']) === strtoupper($setCode)) {
                        $set = $s;
                        break;
                    }
                }
            }

            // Si aucun set trouvé, prendre le premier disponible
            if (!$set && isset($cardData['card_sets'][0])) {
                $set = $cardData['card_sets'][0];
            }

            return [
                'data' => $cardData,
                'set' => $set ?? ['set_code' => 'UNKNOWN'],
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
        'price' => 'nullable|numeric',
        'nm_exemplaire' => 'nullable|integer|min:1',
    ]);

    // Mise à jour de la carte
    $card->update($validated);

    // Redirection vers la page de la collection avec un message de succès
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
