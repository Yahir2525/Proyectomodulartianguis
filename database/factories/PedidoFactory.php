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
            'estado_pedido' => $this->faker->randomElement(['1', '0']),
            'id_compra' => \App\Models\Compra::inRandomOrder()->value('id_compra') ?? null,
            'id_producto' => $productoId = \App\Models\Producto::inRandomOrder()->value('id_producto') ?? null,
            'cantidad' => $this->faker->numberBetween(1, 100),
            'precio_unitario' => \App\Models\Producto::where('id_producto', $productoId)->value('precio_unitario') ?? null,
            'subtotal' => function ($attributes) {
            return $attributes['precio_unitario'] * $attributes['cantidad'];
            },
            'total_pagar' => function ($attributes) {
            return $attributes['subtotal'];
            },

            // 'total_pagar' => function ($attributes) {
            // $total = 0; // Variable estática para acumular los subtotales
            // $total += $attributes['subtotal']; // Sumar el subtotal actual
            // return $total; }// Devolver el total acumulado
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
