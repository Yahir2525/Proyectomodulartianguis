<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Creación de la tabla Productos
        Schema::create('productos', function (Blueprint $table) {
            $table->id('id_producto');
            $table->string('nombre',40);
            $table->string('tipo');
            $table->string('material',20);
            $table->string('color', 10)->nullable();
            $table->string('tamanio', 25);
            $table->string('marca', 25);
            $table->decimal('precio_unitario', 6, 2)->unsigned();
            $table->integer('piezas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
