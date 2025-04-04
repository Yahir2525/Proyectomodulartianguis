<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Cliente;
use App\Models\Abono;
use App\Models\Compra;
use App\Models\Credito;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Vendedor;
use App\Models\Carro;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        // Producto::factory()->count(10)->create();

        // Producto::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // $this->call([
        //     ProductoSeeder::class,
        //     VendedorSeeder::class,
        //     ClienteSeeder::class,
        //     CompraSeeder::class,
        //     CreditoSeeder::class,
        //     AbonoSeeder::class,
        //     PedidoSeeder::class,
        // ]);

        $this->call([
            UserSeeder::class,
            RoleSeeder::class,
            // VendedorSeeder::class,
            // ClienteSeeder::class,
            ProductoSeeder::class,
            CarroSeeder::class,
            CompraSeeder::class,
            PedidoSeeder::class,
            CreditoSeeder::class,
            AbonoSeeder::class,
        ]);

        
    }
}
