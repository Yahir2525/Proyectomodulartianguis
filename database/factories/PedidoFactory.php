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
            'id_compra' => \App\Models\Compra::inRandomOrder()->value('id_compra') ?? null,
            'id_producto' => $productoId = \App\Models\Producto::inRandomOrder()->value('id_producto') ?? null,
            'cantidad' => $this->faker->randomFloat(0, 1, 1000),
            'precio_unitario' => \App\Models\Producto::where('id_producto', $productoId)->value('precio_unitario') ?? null,
            'subtotal' => $this->faker->randomFloat(0, 1, 1000),
            'total_pagar' => $this->faker->randomFloat(2, 1, 1000),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
