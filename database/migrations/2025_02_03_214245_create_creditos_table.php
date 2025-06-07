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
            $table->unsignedBigInteger('id_user')->nullable();
            $table->dateTime('fecha_liquidacion');
            $table->dateTime('fecha_vencimiento');
            $table->boolean('estado');
            $table->decimal('saldo_total', 10,2)->nullable();
            $table->timestamps();


            $table->foreign('id_user')
            ->references('id_user')
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
