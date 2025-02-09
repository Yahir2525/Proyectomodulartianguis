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
            $table->id();
            //Forma de declarar llaves foraneas
            $table->string('nombre_usuario')->nullable();
            $table->foreignId("compra_id")->nullable()->constrained("compras")->onDelete('set null');
            $table->dateTime('fecha_liquidacion');
            $table->dateTime('fecha_vencimiento');
            $table->boolean("estado");
            $table->decimal('saldo_pendiente', 10,2)->unsigned();
            $table->timestamps();

            $table->foreign('nombre_usuario')
            ->references('nombre_usuario')
            ->on('clientes')
            ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('creditos');
    }
};
