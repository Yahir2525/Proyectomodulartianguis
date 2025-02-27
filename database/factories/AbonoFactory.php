<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Abono;
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
            'nombre_usuario' => \App\Models\Cliente::inRandomOrder()->value('nombre_usuario') ?? null,
            'monto_abono' => $this->faker->randomFloat(2, 1, 1000),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
