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
        Schema::create('solicitudes_retiros', function (Blueprint $table) {
            $table->bigIncrements('retiro_id');
            $table->unsignedBigInteger('emprendimientos_id');
            $table->decimal('monto', 12, 2);
            $table->json('cuenta_bancaria');  // alias, número, banco, etc.
            $table->enum('estado', ['pendiente', 'aprobado', 'rechazado', 'pagado'])->default('pendiente');
            $table->string('stripe_transfer_id')->nullable();
            $table->timestamps();

            // Clave foránea
            $table->foreign('emprendimientos_id')
                  ->references('emprendimientos_id')->on('emprendimientos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitudes_retiros');
    }
};
