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
            'id_user' => \App\Models\User::inRandomOrder()->value('id_user') ?? null,
            'estado_pedido' => $this->faker->randomElement(['1', '0']),
        ];
    }
}
