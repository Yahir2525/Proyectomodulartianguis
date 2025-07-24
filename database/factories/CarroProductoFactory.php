<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Carro;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\CarroProducto;
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
        $carro = Carro::inRandomOrder()->first();
        $producto = Producto::inRandomOrder()->first();

        if (!$carro || !$producto) return [];

        // Verificar si el producto ya está en otro carro del mismo pedido
        if ($carro->id_pedido) {
            $carrosDelPedido = Carro::where('id_pedido', $carro->id_pedido)
                ->pluck('id_carro');

            $yaExisteEnPedido = CarroProducto::whereIn('id_carro', $carrosDelPedido)
                ->where('id_producto', $producto->id_producto)
                ->exists();

            if ($yaExisteEnPedido) return [];
        }

        // Verificar disponibilidad
        $reservado = CarroProducto::where('id_producto', $producto->id_producto)->sum('cantidad');
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
