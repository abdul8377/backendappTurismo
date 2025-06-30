<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->bigIncrements('venta_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('metodo_pago_id')->nullable();  // Método de pago, nullable hasta que se procese el pago
            $table->string('codigo_venta')->unique();
            $table->decimal('total', 12, 2); // Total de la venta
            $table->decimal('total_pagado', 12, 2)->default(0); // Total pagado por el cliente
            $table->enum('estado', ['pendiente', 'completado', 'cancelado', 'pagado'])->default('pendiente'); // Estado de la venta
            $table->timestamp('fecha_pago')->nullable(); // Fecha en que se realizó el pago
            $table->timestamps();

            // Relaciones
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('metodo_pago_id')->references('metodo_pago_id')->on('metodo_pagos')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
