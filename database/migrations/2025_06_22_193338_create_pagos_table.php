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
        Schema::create('pagos', function (Blueprint $table) {
            $table->bigIncrements('pago_id');
            $table->unsignedBigInteger('metodo_pago_id');
            $table->unsignedBigInteger('user_id');
            $table->decimal('monto', 10, 2);
            $table->string('referencia')->nullable(); // Ejemplo: número de referencia del pago
            $table->string('estado')->default('pendiente'); // pendiente, completado, fallido, etc.
            $table->timestamps();

            $table->foreign('metodo_pago_id')->references('metodo_pago_id')->on('metodo_pagos');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
