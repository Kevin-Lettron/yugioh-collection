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
            // Ajout du champ 'level' à la table 'cards'
            $table->integer('level')->nullable(); // Le niveau peut être nullable si une carte n'a pas de niveau
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            // Suppression du champ 'level' en cas de rollback de la migration
            $table->dropColumn('level');
        });
    }
};
