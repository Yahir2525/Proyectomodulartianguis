<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Abono;
use App\Models\Cliente;
use App\Models\Compra;
use App\Models\Credito;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Vendedor;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Abono>
 */
class AbonoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Abono::class;

    public function definition(): array
    {
        return [
            // 'nombre_usuario' => Cliente::inRandomOrder()->value('nombre_usuario') ?? null, // Selecciona un cliente aleatorio o NULL
            // 'monto_abono' => $this->faker->randomFloat(2, 50, 1000), // Número decimal entre 50 y 1000
            'id_credito' => \App\Models\Credito::inRandomOrder()->value('id_credito') ?? null,
            // 'id_credito' => $creditoId = \App\Models\Credito::inRandomOrder()->value('id_credito') ?? null,
            'nombre_usuario' => function (array $attributes) {
            return \App\Models\Credito::find($attributes['id_credito'])->nombre_usuario ?? null;},
            'monto_abono' => $this->faker->randomFloat(2, 1, 1000),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
