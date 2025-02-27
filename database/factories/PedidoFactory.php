<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Pedido;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pedido>
 */
class PedidoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    
    protected $model = Pedido::class;
    
    public function definition(): array
    {
        return [
            'id_producto' => \App\Models\Producto::inRandomOrder()->value('id_producto') ?? null,
            'id_compra' => \App\Models\Compra::inRandomOrder()->value('id_compra') ?? null,
            'cantidad' => $this->faker->randomFloat(0, 1, 1000),
            'precio_unitario' => $this->faker->randomFloat(2,1, 1000),
            'subtotal' => $this->faker->randomFloat(0, 1, 1000),
            'total_pagar' => $this->faker->randomFloat(2, 1, 1000),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
