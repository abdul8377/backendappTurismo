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
        Schema::table('conversaciones', function (Blueprint $table) {
            $table->unsignedBigInteger('destinatario_users_id')->nullable()
                ->after('users_id');
            $table->enum('tipo', [
                'usuario_emprendimiento',  // (default actual)
                'usuario_usuario',
                'usuario_moderador'
            ])->default('usuario_emprendimiento')->after('conversaciones_id');

            $table->foreign('destinatario_users_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::table('conversaciones', function (Blueprint $table) {
            $table->dropForeign(['destinatario_users_id']);
            $table->dropColumn(['destinatario_users_id','tipo']);
        });
    }

};
