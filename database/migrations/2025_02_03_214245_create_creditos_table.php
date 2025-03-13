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
        // Creación de la tabla Creditos
        Schema::create('creditos', function (Blueprint $table) {
            $table->id('id_credito');
            //Forma de declarar llaves foraneas
            $table->string('nombre_usuario')->nullable();
            $table->unsignedBigInteger('id_compra')->nullable();
            $table->dateTime('fecha_liquidacion');
            $table->dateTime('fecha_vencimiento');
            $table->boolean('estado');
            $table->decimal('saldo_inicial', 10,2);
            $table->decimal('total_abonado', 10,2);
            $table->decimal('saldo_pendiente', 10,2)->unsigned();
            $table->timestamps();

            $table->foreign('id_compra')->references('id_compra')->on('compras')->onDelete('cascade');

            $table->foreign('nombre_usuario')
            ->references('nombre_usuario')
            ->on('clientes')
            ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    // public function down(): void
    // {
    //     Schema::dropIfExists('creditos');
        
    // }

    public function down(): void {
        Schema::table('creditos', function (Blueprint $table) {
            $table->dropColumn('total_abonado');
        });
    }
};
