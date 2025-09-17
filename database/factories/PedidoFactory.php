<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Abono;
use App\Models\User;
use App\Models\Compra;
use App\Models\DetallePedido;
use App\Models\Credito;
use App\Models\Pedido;
use App\Models\Carro;
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
        if (isset($this->attributes['id_user']) && isset($this->attributes['id_credito'])) {
            return [
                'id_user' => $this->attributes['id_user'],
                'id_credito' => $this->attributes['id_credito'],
                'estado_pedido' => 1,
                'metodo_pago' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $credito = Credito::with('user')->inRandomOrder()->first();

        if (!$credito) {
            $user = User::factory()->create();
            $credito = Credito::factory()->create([
                'id_user' => $user->id_user,
            ]);
        }

        return [
            'id_user' => $credito->id_user,
            'id_credito' => $credito->id_credito,
            'estado_pedido' => 1,
            'metodo_pago' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}