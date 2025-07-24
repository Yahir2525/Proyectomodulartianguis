<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Carro;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\CarroProducto;

class CarroSeeder extends Seeder
{
    public function run(): void
    {
        $pedidos = Pedido::all();
        $productos = Producto::all();

        if ($pedidos->isEmpty() || $productos->isEmpty()) {
            $this->command->warn('No hay pedidos o productos para crear carros.');
            return;
        }

        foreach ($pedidos as $pedido) {
            // Evita duplicar si el pedido ya tiene carro
            if (Carro::where('id_pedido', $pedido->id_pedido)->exists()) {
                continue;
            }

            // Crear el carro asociado a ese pedido
            $carro = Carro::create([
                'id_user' => $pedido->id_user,
                'id_pedido' => $pedido->id_pedido,
            ]);

            // Agrega de 1 a 3 productos distintos
            $productosAleatorios = $productos->random(rand(1, min(3, $productos->count())));

            foreach ($productosAleatorios as $producto) {
                $reservado = CarroProducto::where('id_producto', $producto->id_producto)->sum('cantidad');
                $disponibles = max(0, $producto->piezas - $reservado);

                if ($disponibles <= 0) continue;

                CarroProducto::create([
                    'id_carro' => $carro->id_carro,
                    'id_producto' => $producto->id_producto,
                    'cantidad' => fake()->numberBetween(1, $disponibles),
                ]);
            }
        }
    }
}
