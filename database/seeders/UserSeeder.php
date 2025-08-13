<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserSeeder extends Seeder
{
    public function run()
    {
        $path = storage_path('app/public/usuarios.csv');

        if (!file_exists($path)) {
            $this->command->error("El archivo usuarios.csv no se encontró en storage/app/public/");
            return;
        }

        // Aseguramos que exista el rol 'user'
        $rolUser = Role::firstOrCreate(['name' => 'user']);

        $rows = array_map('str_getcsv', file($path));

        foreach ($rows as $index => $row) {
            if ($index === 0) continue; // Saltar encabezado

            $nombre = $row[0] ?? null;
            $email = $row[1] ?? null;
            $passwordPlano = isset($row[2]) ? trim((string)$row[2]) : '';
            $hashedPassword = (strncmp($passwordPlano, '$2y$', 4) === 0)
                ? $passwordPlano
                : Hash::make($passwordPlano);

            $genero = $row[3] ?? 'O';
            $edad = is_numeric($row[4] ?? null) ? intval($row[4]) : 0;
            $telefono = $row[5] ?? null;
            $direccion = $row[6] ?? null;
            $nombreUsuario = $row[7] ?? null;
            $nombreImagen = $row[8] ?? null;
            $nivel = $row[9] ?? 'bueno';

            $rutaImagen = null;
            if ($nombreImagen) {
                $ruta = public_path('perfiles/' . $nombreImagen);
                if (File::exists($ruta)) {
                    $rutaImagen = 'perfiles/' . $nombreImagen;
                }
            }

            $user = User::create([
                'name' => $nombre,
                'email' => $email,
                'password' => $hashedPassword, // <- del archivo, hasheada
                'genero' => $genero,
                'edad' => $edad,
                'telefono' => $telefono,
                'direccion' => $direccion,
                'nombre_usuario' => $nombreUsuario,
                'imagen' => $rutaImagen,
                'nivel_usuario' => $nivel,
                'dias_aplazo' => 0,

            ]);

            // 🔁 A cada usuario le asignamos el rol
            $user->syncRoles($rolUser);
        }

        $this->command->info('Usuarios importados con rol "user" exitosamente.');
        $this->command->info('Total users: '.\App\Models\User::withoutGlobalScopes()->count());
    }
}
