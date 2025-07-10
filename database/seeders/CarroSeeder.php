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

        // Generamos 30 carros aleatorios
        foreach (range(1, 30) as $i) {
            $pedido = $pedidos->random();

            // Crear el carro vacío (sin productos aún)
            $carro = Carro::create([
                'id_user' => $pedido->id_user,
                'id_pedido' => $pedido->id_pedido,
            ]);

            // Agregamos entre 1 y 3 productos al carro
            $productosAleatorios = $productos->random(rand(1, 3));

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
