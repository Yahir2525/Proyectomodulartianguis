<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Cliente;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cliente>
 */
class ClienteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Cliente::class;
    
    public function definition(): array
    {
        return [
            'nombre' => $this->faker->name(),
            'genero' => $this->faker->randomElement(['M', 'F', 'O']),
            'edad' => $this->faker->numberBetween(18, 120),
            'telefono' => $this->faker->numerify('##########'),
            'direccion' => $this->faker->address(),
            'correo' => $this->faker->unique()->safeEmail(),
            'nombre_usuario' => $this->faker->unique()->userName(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
