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
        Schema::create('productos', function (Blueprint $table) {
            $table->bigIncrements('productos_id');
            $table->unsignedBigInteger('emprendimientos_id');
            $table->string('nombre', 150);
            $table->string('imagen_url', 255)->nullable();
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 10, 2);
            $table->string('unidad', 50)->nullable();
            $table->unsignedBigInteger('categorias_productos_id');
            $table->integer('stock')->default(0);
            $table->integer('capacidad_total')->nullable();

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('emprendimientos_id')->references('emprendimientos_id')->on('emprendimientos');
            $table->foreign('categorias_productos_id')->references('categorias_productos_id')->on('categorias_productos');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
