<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Abono;
use App\Models\User;
use App\Models\Cliente;
use App\Models\Compra;
use App\Models\Credito;
use App\Models\DetallePedido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Vendedor;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Producto>
 */
class ProductoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Producto::class;

    public function definition(): array
    {

        return [
            'nombre' => $this->faker->randomElement(['Colcha', 'Sabana', 'Cortina', 'Toalla']),
            'tipo' => $this->faker->randomElement(['Bata', 'Cobija', 'Mantel', 'Almohada']),
            'material' => $this->faker->randomElement(['Algodon', 'Poliester', 'Fibra']),
            'color' => $this->faker->randomElement(['Rojo', 'Azul', 'Verde', 'Negro', 'Blanco']),
            'tamanio' => $this->faker->randomElement(['S', 'M', 'L', 'XL']),
            'marca' => $this->faker->randomElement(['Pepe', 'Juan', 'Sopas']),
            'precio_unitario' => $this->faker->randomFloat(0, 10, 1000),
            'piezas' => $this->faker->numberBetween(1, 100),
            
        ];
    }
}
