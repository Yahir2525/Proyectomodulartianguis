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
            $numPedidos = rand(4, 5);

            Pedido::factory()
                ->count($numPedidos)
                ->create([
                    'id_user'    => $usuario->id_user,
                    'id_credito' => null,
                ]);
        }

        $this->command->info('Se crearon 4-5 pedidos por usuario.');
    }
}