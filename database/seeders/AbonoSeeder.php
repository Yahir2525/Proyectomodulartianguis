<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Abono;

class AbonoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // DB::table('abonos')->insert([
        //     [
        //         'nombre_usuario' => 'cliente1', // Debe existir en la tabla clientes
        //         'monto_abono' => 150.50,
        //         'created_at' => now(),
        //         'updated_at' => now(),
        //     ],
        //     [
        //         'nombre_usuario' => 'cliente2',
        //         'monto_abono' => 300.75,
        //         'created_at' => now(),
        //         'updated_at' => now(),
        //     ],
        //     [
        //         'nombre_usuario' => null, // Si el cliente fue eliminado
        //         'monto_abono' => 500.00,
        //         'created_at' => now(),
        //         'updated_at' => now(),
        //     ],
        // ]);
        Abono::factory()->count(10)->create();
    }
}
