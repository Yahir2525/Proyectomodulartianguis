<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pedido extends Model
{
    use HasFactory;

    protected $table = 'pedidos';
    protected $primaryKey = 'id_pedido';
    public $incrementing = true;
    protected $keyType = 'int';

        public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class, 'id_compra', 'id_compra');
    }
    //     public function compraPorIdCompra(): BelongsTo
    // {
    //     return $this->belongsTo(Compra::class, 'id_compra', 'id_compra');
    // }
        public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }
    
        public function producto(): HasMany
    {
        return $this->hasMany(Producto::class, 'id_producto', 'id_producto');
    }
}
