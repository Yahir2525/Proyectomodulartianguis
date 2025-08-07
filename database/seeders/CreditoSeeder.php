<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Credito;
use App\Models\User;

class CreditoSeeder extends Seeder
{
    public function run()
    {
        $usuarios = User::all();

        if ($usuarios->isEmpty()) {
            $this->command->warn('No hay usuarios para crear créditos.');
            return;
        }

        foreach ($usuarios as $usuario) {
            // Crear un crédito para cada usuario si no tiene uno aún
            $existeCredito = Credito::where('id_user', $usuario->id_user)->exists();

            if (!$existeCredito) {
                Credito::factory()->create([
                    'id_user' => $usuario->id_user,
                    // otros campos si quieres...
                ]);
            }
        }

        $this->command->info('Se creó un crédito para cada usuario sin crédito.');
    }
}

