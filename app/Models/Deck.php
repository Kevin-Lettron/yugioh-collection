<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deck extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'user_id',
    ];

    public function cards()
    {
        return $this->belongsToMany(Card::class, 'deck_cards')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
