<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetallePedido extends Model
{
    // use HasFactory;

    // protected $table = 'detalle_pedidos';
    // protected $primaryKey = 'id_detalle';
    // public $incrementing = true;
    // protected $keyType = 'int';

    // protected $fillable = [
    // 'id_user',
    // 'id_pedido',
    
    // // otros campos si los hay
    // ];
    

    // protected $attributes = [
    // 'estado_carro' => 1,
    // ];

    // public function user(): BelongsTo
    // {
    //     return $this->belongsTo(User::class, 'id_user', 'id_user');
    // }
    // public function pedido(): BelongsTo
    // {
    //     return $this->belongsTo(Pedido::class, 'id_pedido', 'id_pedido');
    // }
    // public function carro(): HasMany
    // {
    //     return $this->hasMany(Carro::class, 'id_detalle', 'id_detalle');
    // }
}
