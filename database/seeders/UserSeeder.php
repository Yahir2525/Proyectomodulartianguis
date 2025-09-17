<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Storage;  
use Illuminate\Support\Str;


class UserSeeder extends Seeder
{
    public function run()
    {
        $path = storage_path('app/public/usuarios.csv');

        if (!file_exists($path)) {
            $this->command->error("El archivo usuarios.csv no se encontró en storage/app/public/");
            return;
        }

        $rolUser = Role::firstOrCreate(['name' => 'user']);

        $rows = array_map('str_getcsv', file($path));

        foreach ($rows as $index => $row) {
            if ($index === 0) continue;

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
            $nombreImagen    = trim((string)($row[8] ?? ''));
            $nivel = $row[9] ?? 'excelente';

            $rutaImagen = $nombreImagen !== '' ? $nombreImagen : null;

            $user = User::create([
                'name' => $nombre,
                'email' => $email,
                'password' => $hashedPassword,
                'genero' => $genero,
                'edad' => $edad,
                'telefono' => $telefono,
                'direccion' => $direccion,
                'nombre_usuario' => $nombreUsuario,
                'imagen' => $rutaImagen,
                'nivel_usuario' => $nivel,
                'dias_aplazo' => 0,

            ]);

            $user->syncRoles($rolUser);
        }

        $this->command->info('Usuarios importados con rol "user" exitosamente.');
        $this->command->info('Total users: '.\App\Models\User::withoutGlobalScopes()->count());
    }
}