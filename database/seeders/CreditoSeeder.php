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
            // Créditos "activos" (incluye vencidos porque estado = 1)
            $actuales = Credito::where('id_user', $usuario->id_user)
                ->where('estado', 1)
                ->count();

            if ($actuales >= 3) {
                // Ya alcanzó el máximo permitido
                continue;
            }

            // ¿Cuántos crear sin pasar de 3?
            $maxCrear = 3 - $actuales;
            // Si no tiene ninguno, crea entre 1 y $maxCrear; si ya tiene, puede crear 0..$maxCrear
            $aCrear = ($actuales === 0) ? rand(1, $maxCrear) : rand(0, $maxCrear);

            for ($i = 0; $i < $aCrear; $i++) {
                Credito::factory()->create([
                    'id_user' => $usuario->id_user,
                    'estado'  => 1, // activo (si su fecha_vencimiento queda en el pasado, será "vencido")
                ]);
            }
        }

        $this->command->info('Créditos creados respetando: mínimo 1 y máximo 3 activos (incluye vencidos).');
    }
}
