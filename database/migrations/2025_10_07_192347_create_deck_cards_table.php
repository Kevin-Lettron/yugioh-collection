<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deck_cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('deck_id');
            $table->unsignedBigInteger('card_id');
            $table->integer('quantity')->default(1); // nombre d'exemplaires (max 3)
            $table->timestamps();

            $table->foreign('deck_id')->references('id')->on('decks')->onDelete('cascade');
            $table->foreign('card_id')->references('id')->on('cards')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deck_cards');
    }
};
