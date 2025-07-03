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
        Schema::create('carro_productos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_carro')->nullable();
            $table->unsignedBigInteger('id_producto')->nullable();
            $table->integer('cantidad')->nullable();
            $table->timestamps();

            $table->foreign('id_carro')
            ->references('id_carro')
            ->on('carros')
            ->onDelete('cascade');

            $table->foreign('id_producto')
            ->references('id_producto')
            ->on('productos')
            ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carro_productos');
    }
};
