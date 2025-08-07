<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Producto;

class ProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $imagenes = [
            'img/bobcholo.jpeg',
            'img/elda.jpeg',
            'img/elfrecord.png',
            'img/fondo.jpg',
            'img/friends.jpg',
            'img/frieren.jpg',
            'img/frieren3.png',
            'img/iruma.png',
            'img/kanao.jpg',
            'img/kochos.jpg',
            'img/miku.jpg',
            'img/richie.jpg',
            'img/samisopas.jpg',
            'img/shrek.jpeg',
            'img/taylor.jpg',
            'img/toalla.jpeg',
            'img/bobcholo.jpeg',
            'img/elda.jpeg',
            'img/elfrecord.png',
            'img/fondo.jpg',
            'img/friends.jpg',
            'img/frieren.jpg',
            'img/frieren3.png',
            'img/iruma.png',
            'img/kanao.jpg',
            'img/kochos.jpg',
            'img/miku.jpg',
            'img/richie.jpg',
            'img/samisopas.jpg',
            'img/shrek.jpeg',
            'img/taylor.jpg',
            'img/toalla.jpeg',
        ];

        foreach ($imagenes as $imagen) {
            Producto::factory()->create([
                'imagen' => $imagen
            ]);
        }

        
    }
}
