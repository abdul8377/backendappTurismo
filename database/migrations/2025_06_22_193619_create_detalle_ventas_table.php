<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_ventas', function (Blueprint $table) {
            $table->bigIncrements('detalle_venta_id');
            $table->unsignedBigInteger('venta_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('productos_id')->nullable();
            $table->unsignedBigInteger('servicios_id')->nullable();
            $table->integer('cantidad')->default(1);
            $table->decimal('precio_unitario', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->decimal('descuento', 12, 2)->default(0); // Descuento aplicado a cada ítem
            $table->decimal('impuesto', 12, 2)->default(0); // Impuesto aplicado a cada ítem
            $table->timestamps();

            // Relaciones
            $table->foreign('venta_id')->references('venta_id')->on('ventas')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('productos_id')->references('productos_id')->on('productos')->onDelete('set null');
            $table->foreign('servicios_id')->references('servicios_id')->on('servicios')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_ventas');
    }
};
