<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Credito;

class CreditoSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = User::all();

        if ($usuarios->isEmpty()) {
            $this->command->warn('No hay usuarios para crear créditos.');
            return;
        }

        foreach ($usuarios as $usuario) {
            $actuales = Credito::where('id_user', $usuario->id_user)
                ->where('estado', 1)
                ->count();

            if ($actuales >= 3) {
                continue;
            }

            $maxCrear = 3 - $actuales;
            $aCrear = ($actuales === 0) ? rand(1, $maxCrear) : rand(0, $maxCrear);

            for ($i = 0; $i < $aCrear; $i++) {
                Credito::factory()->create([
                    'id_user' => $usuario->id_user,
                    'estado'  => 1,
                ]);
            }
        }

        $this->command->info('Créditos creados respetando: mínimo 1 y máximo 3 activos (incluye vencidos).');
    }
}