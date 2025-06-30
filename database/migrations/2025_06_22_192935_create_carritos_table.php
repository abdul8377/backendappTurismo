<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carritos', function (Blueprint $table) {
            $table->bigIncrements('carrito_id'); // ID único del carrito
            $table->unsignedBigInteger('user_id'); // ID del usuario que tiene el carrito
            $table->unsignedBigInteger('productos_id')->nullable(); // Producto (si aplica)
            $table->unsignedBigInteger('servicios_id')->nullable(); // Servicio (si aplica)
            $table->integer('cantidad')->default(1); // Cantidad de producto o servicio
            $table->decimal('precio_unitario', 12, 2); // Precio unitario de producto o servicio
            $table->decimal('subtotal', 12, 2); // Subtotal de cada ítem (cantidad * precio_unitario)
            $table->decimal('total_carrito', 12, 2)->nullable(); // Total del carrito (acumulado)
            $table->enum('estado', ['en proceso', 'completado'])->default('en proceso'); // Estado del carrito
            $table->timestamps(); // Fechas de creación y actualización

            // Relaciones de claves foráneas
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('productos_id')->references('productos_id')->on('productos')->onDelete('set null');
            $table->foreign('servicios_id')->references('servicios_id')->on('servicios')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carritos');
    }
};
