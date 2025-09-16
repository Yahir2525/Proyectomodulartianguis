<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Abono;
use App\Models\Credito;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Carro;
use App\Models\CarroProducto;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            ProductoSeeder::class,
            CreditoSeeder::class,
            PedidoSeeder::class,
            CarroSeeder::class,
            CarroProductoSeeder::class,
            // AbonoSeeder::class,
        ]);

    }
}
