<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Carro;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Abono;
use App\Models\Credito;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Carro>
 */
class CarroFactory extends Factory
{
    public function definition(): array
    {
        $pedido = \App\Models\Pedido::inRandomOrder()->first();

        if (!$pedido) return [];

        return [
            'id_user' => $pedido->id_user,
            'id_pedido' => $pedido->id_pedido,
        ];
    }
}
