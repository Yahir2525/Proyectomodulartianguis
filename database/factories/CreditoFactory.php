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
        return [
            'nombre_usuario' => function (array $attributes) {
            return \App\Models\Compra::find($attributes['id_compra'])->nombre_usuario ?? null;},
            'id_compra' => \App\Models\Compra::inRandomOrder()->value('id_compra') ?? null,
            'fecha_liquidacion' => $this->faker->dateTime(),
            'fecha_vencimiento' => $this->faker->dateTimeBetween('2020-01-01', '2024-12-31'),
            'estado' => $this->faker->randomElement([true, false]),
            'saldo_inicial' => $credito->saldo_inicial ?? 0,
            'total_abonado' => $credito->total_abonado ?? 0, 
            'saldo_pendiente' => $credito->saldo_pendiente ?? 0,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
