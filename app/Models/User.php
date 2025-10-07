<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Les attributs modifiables en masse.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'has_new_follower', // 👈 ajouté ici
    ];

    /**
     * Les attributs cachés pour la sérialisation.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Les attributs convertis automatiquement.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'has_new_follower' => 'boolean', // 👈 important pour éviter un cast en string
        ];
    }

    // 🔹 Relations
    public function cards()
    {
        return $this->hasMany(Card::class);
    }

    public function decks()
    {
        return $this->hasMany(Deck::class);
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'follows', 'followed_id', 'follower_id')
                    ->withTimestamps();
    }

    public function following()
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'followed_id')
                    ->withTimestamps();
    }

    // 🔹 Méthodes utilitaires
    public function isFollowing(User $user): bool
    {
        return $this->following->contains($user->id);
    }

    public function isFollowedBy(User $user): bool
    {
        return $this->followers->contains($user->id);
    }
}
