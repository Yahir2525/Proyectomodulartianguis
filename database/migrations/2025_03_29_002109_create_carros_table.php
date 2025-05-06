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
        Schema::create('carros', function (Blueprint $table) {
            $table->id('id_carro');
            $table->string('nombre_usuario')->nullable();
            $table->unsignedBigInteger('id_compra')->nullable();
            $table->unsignedBigInteger('id_producto')->nullable();
            $table->integer('cantidad');
            $table->boolean('estado_producto');
            $table->timestamps();

            $table->foreign('nombre_usuario')->references('nombre_usuario')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('id_compra')->references('id_compra')->on('compras')->onDelete('cascade');
            $table->foreign('id_producto')->references('id_producto')->on('productos')->onDelete('cascade');
            
        }); 
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carros');
    }
};
