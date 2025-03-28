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
            $table->unsignedBigInteger('id_compra')->nullable();
            $table->unsignedBigInteger('id_producto')->nullable();
            $table->integer('cantidad')->unsigned();
            $table->decimal('precio_unitario', 10, 2)->unsigned()->default(0);
            $table->decimal('subtotal', 10, 2)->unsigned();
            $table->decimal('total_pagar', 10,2)->unsigned();
            $table->timestamps();

            $table->foreign('id_compra')->references('id_compra')->on('compras')->onDelete('cascade');
            $table->foreign('id_producto')->references('id_producto')->on('productos')->onDelete('cascade');

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
