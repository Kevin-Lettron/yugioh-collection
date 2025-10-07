<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relation : un utilisateur possède plusieurs cartes.
     */
    public function cards()
    {
        return $this->hasMany(Card::class);
    }

    /**
     * Relation : un utilisateur possède plusieurs decks.
     */
    public function decks()
    {
        return $this->hasMany(Deck::class);
    }

    /**
     * Relation : utilisateurs qui me suivent.
     */
    public function followers()
    {
        return $this->belongsToMany(User::class, 'follows', 'followed_id', 'follower_id')
                    ->withTimestamps();
    }

    /**
     * Relation : utilisateurs que je suis.
     */
    public function following()
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'followed_id')
                    ->withTimestamps();
    }

    /**
     * Vérifie si l'utilisateur actuel suit un autre utilisateur.
     */
    public function isFollowing(User $user): bool
    {
        return $this->following->contains($user->id);
    }

    /**
     * Vérifie si un utilisateur est suivi par un autre.
     */
    public function isFollowedBy(User $user): bool
    {
        return $this->followers->contains($user->id);
    }
}
