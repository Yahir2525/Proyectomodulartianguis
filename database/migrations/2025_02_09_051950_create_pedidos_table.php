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
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id('id_pedido');
            $table->unsignedBigInteger('id_user')->nullable();
            $table->unsignedBigInteger('id_credito')->nullable();
            $table->decimal('total_pedido')->nullable();
            $table->boolean('estado_pedido')->default(1);
            $table->char('metodo_pago', 7);
            $table->timestamps();

            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('cascade');
            $table->foreign('id_credito')->references('id_credito')->on('creditos')->onDelete('cascade');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
