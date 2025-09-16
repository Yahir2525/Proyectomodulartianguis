<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;  
use Illuminate\Support\Str;


class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        $path = storage_path('app/public/inventario.csv');

        if (!file_exists($path)) {
            $this->command->error('El archivo inventario.csv no se encontró en storage/app/public/');
            return;
        }

        $rows = array_map('str_getcsv', file($path));

        foreach ($rows as $index => $row) {
            if ($index === 0) continue;
            if (count($row) < 9) continue;

            $nombre          = $row[0] ?? null;
            $tipo            = $row[1] ?? null;
            $material        = $row[2] ?? null;
            $color           = $row[3] ?? null;
            $tamanio         = $row[4] ?? null;
            $marca           = $row[5] ?? null;
            $precioUnitario  = (float) ($row[6] ?? 0);
            $piezas          = (int)   ($row[7] ?? 0);
            $nombreImagen    = trim((string)($row[8] ?? ''));

            $rutaImagen = $nombreImagen !== '' ? $nombreImagen : null;

            Producto::create([
                'nombre'          => $nombre,
                'tipo'            => $tipo,
                'material'        => $material,
                'color'           => $color,
                'tamanio'         => $tamanio,
                'marca'           => $marca,
                'precio_unitario' => $precioUnitario,
                'piezas'          => $piezas,
                'imagen'          => $rutaImagen,
                'estado_producto' => true,
            ]);
        }

        $this->command->info('Productos importados exitosamente.');
    }
}