<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Abono;
use App\Models\User;
use App\Models\Credito;

class AbonoFactory extends Factory
{
    protected $model = Abono::class;

    public function definition(): array
    {
        // 1) Tomar un crédito existente y usar SU usuario
        $credito = Credito::inRandomOrder()->first();

        // 2) Si no hay créditos aún, crear user + crédito ligados
        if (!$credito) {
            $user = User::factory()->create();
            $credito = Credito::factory()->create([
                'id_user' => $user->id_user,
            ]);
        }

        return [
            // Por defecto: mismo user del crédito
            'id_credito'   => $credito->id_credito,
            'id_user'      => $credito->id_user,
            'monto_abono' => $this->faker->numberBetween(1, 2000),
            'created_at'   => now(),
            'updated_at'   => now(),
        ];
    }
}
