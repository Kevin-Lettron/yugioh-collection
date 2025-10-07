<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            // Vérifiez si la colonne existe avant de l'ajouter
            if (!Schema::hasColumn('cards', 'ucard_id')) {
                $table->string('ucard_id')->nullable(); // Ajout de la colonne 'ucard_id'
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            // Supprimer la colonne 'ucard_id' si elle existe
            if (Schema::hasColumn('cards', 'ucard_id')) {
                $table->dropColumn('ucard_id');
            }
        });
    }
};
