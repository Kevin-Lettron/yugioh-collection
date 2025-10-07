<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('card_deck', function (Blueprint $table) {
            $table->id();

            // Liens vers les tables existantes
            $table->foreignId('card_id')->constrained('cards')->onDelete('cascade');
            $table->foreignId('deck_id')->constrained('decks')->onDelete('cascade');

            // Quantité de cette carte dans le deck
            $table->integer('quantity')->default(1);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_deck');
    }
};
