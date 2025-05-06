<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';
    protected $primaryKey = 'id_producto';
    public $incrementing = true;
    protected $keyType = 'int';

    public function pedidos(): BelongsTo
    {
        return $this->belongsToMany(Producto::class, 'pedidos', 'id_producto', 'id_pedido')->withPivot('cantidad');
    }

    public function carros(): BelongsToMany
    {
        return $this->belongsToMany(Carro::class, 'carros', 'id_producto', 'id_carro')->withPivot('cantidad');
    }
}
