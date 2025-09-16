<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Carro;
use App\Models\Producto;
use App\Models\CarroProducto;

class CarroProductoFactory extends Factory
{
    protected $model = CarroProducto::class;

    public function definition(): array
    {
        $carro = Carro::inRandomOrder()->first();
        $producto = Producto::where('piezas', '>', 0)->inRandomOrder()->first();

        if (!$carro || !$producto) return [];

        if ($carro->id_pedido) {
            $carrosDelPedido = Carro::where('id_pedido', $carro->id_pedido)->pluck('id_carro');
            $yaExisteEnPedido = CarroProducto::whereIn('id_carro', $carrosDelPedido)
                ->where('id_producto', $producto->id_producto)
                ->exists();
            if ($yaExisteEnPedido) return [];
        }

        $reservado   = CarroProducto::where('id_producto', $producto->id_producto)->sum('cantidad');
        $disponibles = max(0, $producto->piezas - $reservado);

        if ($disponibles < 1) return [];

        return [
            'id_carro'    => $carro->id_carro,
            'id_producto' => $producto->id_producto,
            'cantidad'    => $this->faker->numberBetween(1, $disponibles),
            'created_at'  => now(),
            'updated_at'  => now(),
        ];
    }
}