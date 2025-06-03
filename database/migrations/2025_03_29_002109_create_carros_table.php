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
            $table->unsignedBigInteger('id_user')->nullable();
            $table->unsignedBigInteger('id_pedido')->nullable();
            $table->unsignedBigInteger('id_producto')->nullable();
            $table->integer('cantidad');
            $table->timestamps();

            $table->foreign('id_user')
            ->references('id_user')
            ->on('users')
            ->onDelete('cascade');
            
            $table->foreign('id_pedido')->references('id_pedido')->on('pedidos')->onDelete('cascade');
            
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
