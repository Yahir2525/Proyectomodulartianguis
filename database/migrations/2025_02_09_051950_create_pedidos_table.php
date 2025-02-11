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
            $table->id();
            $table->foreignId('compra_id')->nullable()->constrained('compras')->onDelete('set null');
            $table->foreignId('producto_id')->nullable()->constrained('productos')->onDelete('cascade');
            $table->integer('cantidad')->unsigned();
            $table->decimal('precio_unitario', 10, 2)->unsigned()->default(0);
            $table->decimal('subtotal ', 10, 2)->unsigned();
            $table->decimal('total_pagar', 10,2)->unsigned();
            $table->timestamps();


            // $table->foreign('precio_unitario')
            // ->references('precio_unitario')
            // ->on('productos')
            // ->onDelete('set default');
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
