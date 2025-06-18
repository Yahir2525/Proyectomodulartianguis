<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Abono;
use App\Models\User;
use App\Models\Compra;
use App\Models\Carro;
use App\Models\Credito;
use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Vendedor;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Carro>
 */
class CarroFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $detalle = \App\Models\DetallePedido::inRandomOrder()->first();
        return [
            'id_user' => $detalle?->id_user,
            'id_detalle' => $detalle?->id_detalle,
            'id_producto' => $producto = Producto::inRandomOrder()->first()?->id_producto ?? null,
            'cantidad' => $this->faker->numberBetween(1, 100),
        ];
    }
}
