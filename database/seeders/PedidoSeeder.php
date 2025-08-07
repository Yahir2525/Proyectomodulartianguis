<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Pedido;

class PedidoSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = User::all();

        if ($usuarios->isEmpty()) {
            $this->command->warn('No hay usuarios para crear pedidos.');
            return;
        }

        foreach ($usuarios as $usuario) {
            Pedido::factory()->create([
                'id_user' => $usuario->id_user, // 👈 asegúrate de que sea 'id_user'
                // Si quieres que todos empiecen sin crédito:
                'id_credito' => null,
            ]);
        }

        $this->command->info('Se creó un pedido para cada usuario.');
    }
}
