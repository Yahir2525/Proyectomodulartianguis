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
        // Creación de la tabla Abonos
        Schema::create('abonos', function (Blueprint $table) {
            $table->id('id_abono');
            //Forma de declarar llaves foraneas
            $table->unsignedBigInteger('id_credito')->nullable();
            $table->string('nombre_usuario')->nullable();
            $table->decimal('monto_abono', 10,2)->unsigned();
            $table->timestamps();

            $table->foreign('id_credito')->references('id_credito')->on('creditos')->onDelete('cascade');

            $table->foreign('nombre_usuario')
            ->references('nombre_usuario')
            ->on('users')
            ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('abonos');
    }
};
