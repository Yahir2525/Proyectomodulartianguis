<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Carro;
use App\Models\Pedido;
use App\Models\Producto;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CarroProducto>
 */
class CarroProductoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $carro = \App\Models\Carro::inRandomOrder()->first();
        $producto = \App\Models\Producto::inRandomOrder()->first();

        if (!$carro || !$producto) return [];

        // Calcular lo reservado en todos los carros para ese producto
        $reservado = \App\Models\CarroProducto::where('id_producto', $producto->id_producto)->sum('cantidad');

        $disponibles = max(0, $producto->piezas - $reservado);

        if ($disponibles <= 0) return [];

        return [
            'id_carro' => $carro->id_carro,
            'id_producto' => $producto->id_producto,
            'cantidad' => $this->faker->numberBetween(1, $disponibles),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
