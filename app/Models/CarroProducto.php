<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CarroProducto extends Model
{
    use HasFactory;

    protected $table = 'carro_productos';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = ['id_carro', 'id_producto', 'cantidad'];
}
