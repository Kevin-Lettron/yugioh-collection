<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    protected $fillable = [
        'code',
        'name',
        'card_type',
        'description',
        'atk',
        'def',
        'rarity',
        'price',
        'set_code', // Série de la carte
        'ucard_id', // ID unique de la carte
        'user_id',
        'level', // Niveau de la carte
        'nm_exemplaire', // Nombre d'exemplaires
    ];
}
