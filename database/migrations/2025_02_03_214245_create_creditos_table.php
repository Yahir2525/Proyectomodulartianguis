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
            $table->foreignId("cliente_id")->constrained("clientes")->onDelete('set null');
            $table->foreignId("compra_id")->constrained("compras")->onDelete('set null');
            $table->dateTime('fecha_liquidacion');
            $table->dateTime('fecha_vencimiento');
            $table->boolean("estado");
            $table->decimal('total_pagar', 10,2)->unsigned();
            $table->decimal('saldo_pendiente', 10,2)->unsigned();
            $table->timestamps();

            //Forma de declarar llaves foranea que no sea el ID
            // $table->foreign('compra_id')->references('total_pagar')->on('compras')->onDelete('set null');
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
