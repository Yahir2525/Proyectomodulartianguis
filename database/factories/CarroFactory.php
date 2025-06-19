<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Carro;
use App\Models\Pedido;
use App\Models\Producto;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Carro>
 */
class CarroFactory extends Factory
{
    public function definition(): array
    {
        $pedido = Pedido::inRandomOrder()->first();
        $producto = Producto::inRandomOrder()->first();

        if (!$pedido || !$producto) {
            return [];
        }

        // Total reservado actualmente para este producto en todos los carros
        $reservado = Carro::where('id_producto', $producto->id_producto)->sum('cantidad');

        // Calcula piezas disponibles
        $piezasDisponibles = max(0, $producto->piezas - $reservado);

        if ($piezasDisponibles <= 0) {
            return []; // No hay disponibilidad, no creamos este carro
        }

        return [
            'id_user' => $pedido->id_user,
            'id_pedido' => $pedido->id_pedido,
            'id_producto' => $producto->id_producto,
            'cantidad' => fake()->numberBetween(1, $piezasDisponibles),
        ];
    }
}
