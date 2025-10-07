<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cards', function (Blueprint $table) {
            $table->id(); // Utilise `id()` pour une clé primaire auto-incrémentée
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('code')->unique(); // Code complet pour la carte (ID-SETCODE)
            $table->string('name');
            $table->string('card_type');
            $table->text('description')->nullable();
            $table->integer('level')->nullable(); // Champ pour le niveau, nullable si ce n'est pas un monstre
            $table->integer('atk')->nullable();
            $table->integer('def')->nullable();
            $table->string('rarity')->nullable();
            $table->decimal('price', 8, 2)->nullable();
            $table->string('set_code'); // Code du set
            $table->string('ucard_id'); // ID unique de la carte pour recherche API, non nullable
            $table->string('image_url')->nullable(); // URL de l'image de la carte
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
