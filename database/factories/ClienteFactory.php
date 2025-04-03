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
            'id_user' => \App\Models\User::inRandomOrder()->value('id_user') ?? null,
            'nombre' => $this->faker->name(),
            'genero' => $this->faker->randomElement(['M', 'F', 'O']),
            'edad' => $this->faker->numberBetween(18, 120),
            'telefono' => $this->faker->numerify('##########'),
            'direccion' => $this->faker->address(),
            'nombre_usuario' => $this->faker->unique()->userName(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
