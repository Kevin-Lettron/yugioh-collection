<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Card extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'card_type',
        'description',
        'atk',
        'def',
        'rarity',
        'price',
        'set_code',
        'ucard_id',
        'user_id',
        'level',
        'nm_exemplaire',
    ];

    public function decks()
    {
        return $this->belongsToMany(Deck::class, 'deck_cards')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calcule la quantité disponible d'une carte pour un utilisateur :
     * nm_exemplaire - cartes déjà utilisées dans les autres decks.
     */
    public function availableQuantityForUser(int $userId, ?int $excludeDeckId = null): int
    {
        $owned = (int) ($this->nm_exemplaire ?? 0);

        $usedQuery = DB::table('deck_cards')
            ->join('decks', 'deck_cards.deck_id', '=', 'decks.id')
            ->where('decks.user_id', $userId)
            ->where('deck_cards.card_id', $this->id);

        if ($excludeDeckId) {
            $usedQuery->where('deck_cards.deck_id', '!=', $excludeDeckId);
        }

        $used = (int) $usedQuery->sum('deck_cards.quantity');

        return max(0, $owned - $used);
    }
}
