<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Abono;
use App\Models\User;
use App\Models\Compra;
use App\Models\Credito;
use App\Models\Pedido;
use App\Models\DetallePedido;
use App\Models\Producto;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Credito>
 */
class CreditoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Credito::class;

    public function definition(): array
    {
        // Obtener un usuario aleatorio, si no hay crear uno
        $userId = User::inRandomOrder()->value('id_user');
        if (!$userId) {
            $user = User::factory()->create();
            $userId = $user->id_user;
        }

        return [
            'id_user' => $userId,
            'fecha_liquidacion' => null,
            'fecha_vencimiento' => $this->faker->dateTimeBetween('2020-01-01', '2025-12-31'),
            'estado' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

}
