<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;   

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
            $nombreImagen    = $row[8] ?? null;

            $rutaImagen = null;

            if ($nombreImagen) {
                $rutaLocal = public_path('img/' . $nombreImagen);

                if (File::exists($rutaLocal)) {
                    // Guardamos la MISMA ruta relativa en BD
                    $rutaImagen = 'img/' . $nombreImagen;

                    // Solo si el disk activo es s3, subimos el archivo al bucket
                    if (config('filesystems.default') === 's3') {
                        $destino = $rutaImagen; // "img/archivo.jpg"

                        // Evita re-subir si ya existe
                        if (!Storage::disk('s3')->exists($destino)) {
                            Storage::disk('s3')->put(
                                $destino,
                                File::get($rutaLocal),
                                ['visibility' => 'private'] // bucket privado
                            );
                        }
                    }
                }
            }

            Producto::create([
                'nombre'          => $nombre,
                'tipo'            => $tipo,
                'material'        => $material,
                'color'           => $color,
                'tamanio'         => $tamanio,
                'marca'           => $marca,
                'precio_unitario' => $precioUnitario,
                'piezas'          => $piezas,
                'imagen'          => $rutaImagen,   // sigue siendo "img/archivo.jpg"
                'estado_producto' => true,
            ]);
        }

        $this->command->info('Productos importados exitosamente.');
    }
}
