<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Création d’un utilisateur de test avec tes identifiants
        $user = User::factory()->create([
            'name' => 'keito',
            'email' => 'kevinlettron@outlook.fr',
            'password' => bcrypt('K4llMinight4080.'), // mot de passe fourni
        ]);

        // On exécute le seeder des cartes
        $this->call(CardSeeder::class);
    }
}
