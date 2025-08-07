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
            // Asegurarse de que tenga al menos un pedido
            $pedido = $usuario->pedido()->first();
            if (!$pedido) {
                $pedido = Pedido::factory()->create([
                    'id_usuario' => $usuario->id,
                ]);
            }

            // Asegurarse de que tenga un carro (1:1 por pedido)
            $carro = $pedido->carro;
            if (!$carro) {
                $carro = Carro::factory()->create([
                    'id_pedido' => $pedido->id_pedido,
                ]);
            }

            // Crear al menos un producto del carro
            CarroProducto::factory()->create([
                'id_carro' => $carro->id_carro,
                'id_producto' => $productos->random()->id_producto,
            ]);
        }
    }
}
