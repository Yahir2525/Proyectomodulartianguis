<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Abono;
use App\Models\Usuario;
use App\Models\Compra;
use App\Models\Credito;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Vendedor;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Credito>
 */
class CreditoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Credito::class;

    public function definition(): array
    {
        $compra = \App\Models\Compra::inRandomOrder()->first();
        return [
            'id_user' => \App\Models\User::inRandomOrder()->value('id_user') ?? null, 
            'fecha_liquidacion' => $this->faker->dateTime(),
            'fecha_vencimiento' => $this->faker->dateTimeBetween('2020-01-01', '2024-12-31'),
            'estado' => $this->faker->randomElement([true, false]),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
