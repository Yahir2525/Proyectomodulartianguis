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
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Compra>
 */
class CompraFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Compra::class;
    
    public function definition(): array
    {
        return [
            'nombre_usuario' => \App\Models\Cliente::inRandomOrder()->value('nombre_usuario') ?? null,
            'estado_compra' => $this->faker->randomElement(['1', '0']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
