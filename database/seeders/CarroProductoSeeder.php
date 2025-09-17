<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CarroProducto;
use App\Models\User;
use App\Models\Pedido;
use App\Models\Carro;
use App\Models\Producto;

class CarroProductoSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = User::all();
        $productos = Producto::all();

        foreach ($usuarios as $usuario) {
            $pedido = $usuario->pedido()->first();
            if (!$pedido) {
                $pedido = Pedido::factory()->create([
                    'id_usuario' => $usuario->id,
                ]);
            }

            $carro = $pedido->carro;
            if (!$carro) {
                $carro = Carro::factory()->create([
                    'id_pedido' => $pedido->id_pedido,
                ]);
            }

            CarroProducto::factory()->create([
                'id_carro' => $carro->id_carro,
                'id_producto' => $productos->random()->id_producto,
            ]);
        }
    }
}