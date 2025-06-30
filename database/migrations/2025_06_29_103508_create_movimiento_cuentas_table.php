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
        Schema::create('movimiento_cuentas', function (Blueprint $table) {
          $table->bigIncrements('movimiento_id');
            $table->unsignedBigInteger('emprendimientos_id');
            $table->unsignedBigInteger('venta_id')->nullable();
            $table->unsignedBigInteger('detalle_venta_id')->nullable();
            $table->enum('tipo', ['venta', 'comision', 'ajuste', 'retiro']); // naturaleza
            $table->decimal('monto', 12, 2);                                 // positivo o negativo
            $table->enum('estado', [
                'pendiente',
                'liberado',
                'en_retiro',
                'pagado',
                'cancelado'
            ])->default('pendiente');
            $table->string('stripe_id')->nullable();   // id de Charge, Transfer, Payout, etc.
            $table->timestamps();

            // claves foráneas
            $table->foreign('emprendimientos_id')->references('emprendimientos_id')->on('emprendimientos');
            $table->foreign('venta_id')->references('venta_id')->on('ventas');
            $table->foreign('detalle_venta_id')->references('detalle_venta_id')->on('detalle_ventas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimiento_cuentas');
    }
};
