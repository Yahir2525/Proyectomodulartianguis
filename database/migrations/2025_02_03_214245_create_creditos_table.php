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
            $table->string('nombre_usuario')->nullable();
            $table->dateTime('fecha_liquidacion');
            $table->dateTime('fecha_vencimiento');
            $table->boolean('estado');
            $table->decimal('saldo_total', 10,2)->nullable();
            $table->decimal('total_abonado', 10,2);
            $table->decimal('saldo_pendiente', 10,2)->nullable();
            $table->timestamps();


            $table->foreign('nombre_usuario')
            ->references('nombre_usuario')
            ->on('users')
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
