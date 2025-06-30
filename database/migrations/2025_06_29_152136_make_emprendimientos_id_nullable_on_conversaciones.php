<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversaciones', function (Blueprint $table) {
            // requiere doctrine/dbal en composer para usar ->change()
            $table->unsignedBigInteger('emprendimientos_id')
                  ->nullable()        // ← ahora puede ser NULL
                  ->change();
        });
    }

    public function down(): void
    {
        Schema::table('conversaciones', function (Blueprint $table) {
            $table->unsignedBigInteger('emprendimientos_id')
                  ->nullable(false)
                  ->change();
        });
    }
};
