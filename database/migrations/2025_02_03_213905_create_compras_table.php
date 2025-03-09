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
            // $table->unsignedBigInteger("id_pedido")->nullable();
            // if (Schema::hasTable('pedidos')) {
            //     $table->foreign("id_pedido")->references("id_pedido")->on("pedidos")->onDelete("set cascade");
            // }
            $table->string('nombre_usuario')->nullable();
            $table->boolean("estado_compra");
            $table->timestamps();

            // $table->foreign('id_pedido')->references('id_pedido')->on('pedidos')->onDelete('cascade');

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
