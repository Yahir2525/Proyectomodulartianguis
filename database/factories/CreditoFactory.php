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
        $compra = \App\Models\Compra::inRandomOrder()->first();
        return [
            'id_compra' => $compra?->id_compra,
            'nombre_usuario' => $compra?->nombre_usuario, 
            'fecha_liquidacion' => $this->faker->dateTime(),
            'fecha_vencimiento' => $this->faker->dateTimeBetween('2020-01-01', '2024-12-31'),
            'estado' => $this->faker->randomElement([true, false]),
            'saldo_total' => $credito->saldo_total ?? 0,
            'total_abonado' => $credito->total_abonado ?? 0,
            'saldo_pendiente' => $credito->saldo_pendiente ?? 0, 
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
