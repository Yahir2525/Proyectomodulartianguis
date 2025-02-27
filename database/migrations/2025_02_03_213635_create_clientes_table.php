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
        // Creación de la tabla Clientes/Usuarios
        Schema::create('clientes', function (Blueprint $table) {
            $table->id('id_cliente');
            $table->string('nombre',40);
            $table->char('genero',1)->default('O');
            $table->integer('edad')->unsigned();
            $table->string('telefono');
            $table->string('direccion',80);
            $table->string('correo',40);
            $table->string('nombre_usuario')->unique();
            // $table->string('contrasenia',40);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
