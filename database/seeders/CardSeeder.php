<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Card;
use Illuminate\Support\Facades\Auth;

class CardSeeder extends Seeder
{
    public function run(): void
    {
        // ⚠️ Si tu veux lier à un utilisateur spécifique
        // Mets ici un ID utilisateur existant
        $userId = 1;

        $types = ['Normal', 'Effect', 'Fusion', 'Ritual', 'Synchro', 'XYZ', 'Link'];
        $rarities = ['Common', 'Super Rare', 'Ultra Rare', 'Secret Rare'];

        for ($i = 1; $i <= 45; $i++) {
            Card::create([
                'ucard_id' => 10000 + $i,
                'set_code' => 'SET' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'code' => (10000 + $i) . '-SET' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'name' => 'Carte Test ' . $i,
                'card_type' => $types[array_rand($types)],
                'description' => 'Ceci est une carte de test générée automatiquement pour les essais de deck.',
                'atk' => rand(500, 4000),
                'def' => rand(500, 4000),
                'rarity' => $rarities[array_rand($rarities)],
                'price' => rand(100, 2000) / 100, // prix entre 1.00€ et 20.00€
                'level' => rand(1, 12),
                'user_id' => $userId,
                'nm_exemplaire' => rand(1, 3),
            ]);
        }
    }
}
