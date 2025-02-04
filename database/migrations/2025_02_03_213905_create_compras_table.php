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
            $table->id();
            //Forma de declarar llaves foraneas
            $table->foreignId("cliente_id")->constrained("clientes")->onDelete('set null');
            $table->decimal('total_pagar', 10,2)->unsigned();
            $table->timestamps();
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
