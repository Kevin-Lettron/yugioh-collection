<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            // Ajouter la colonne `set_code` si elle n'existe pas déjà
            if (!Schema::hasColumn('cards', 'set_code')) {
                $table->string('set_code')->nullable();
            }

            // Ajouter la colonne `ucard_id` si elle n'existe pas déjà
            if (!Schema::hasColumn('cards', 'ucard_id')) {
                $table->string('ucard_id')->nullable(false); // ID unique pour API
            }
        });
    }

    public function down(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            if (Schema::hasColumn('cards', 'set_code')) {
                $table->dropColumn('set_code');
            }
            if (Schema::hasColumn('cards', 'ucard_id')) {
                $table->dropColumn('ucard_id');
            }
        });
    }
};
