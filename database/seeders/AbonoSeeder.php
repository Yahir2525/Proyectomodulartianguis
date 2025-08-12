<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Abono;
use App\Models\User;
use App\Models\Credito;

class AbonoSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = User::all();

        if ($usuarios->isEmpty()) {
            $this->command->warn('No hay usuarios para crear abonos.');
            return;
        }

        foreach ($usuarios as $usuario) {
            // Obtener (o crear) un crédito del usuario
            $credito = Credito::where('id_user', $usuario->id_user)->first();

            if (!$credito) {
                $credito = Credito::factory()->create([
                    'id_user' => $usuario->id_user,
                ]);
            }

            // Crear 2 o 3 abonos para este crédito
            $numAbonos = rand(2, 4);

            Abono::factory()
                ->count($numAbonos)
                ->create([
                    'id_credito' => $credito->id_credito,
                    'id_user'    => $usuario->id_user,
                ]);
        }

        $this->command->info('Se crearon 2–3 abonos por usuario.');
    }
}
