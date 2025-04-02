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
        // Creación de la tabla Compras
        Schema::create('compras', function (Blueprint $table) {
            $table->id('id_compra');
            $table->string('nombre_usuario')->nullable();
            $table->decimal('total_compra')->nullable();
            $table->boolean("estado_compra");
            $table->timestamps();

            $table->foreign('nombre_usuario')
            ->references('nombre_usuario')
            ->on('clientes')
            ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compras');
    }
};
