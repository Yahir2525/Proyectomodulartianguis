<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pedido extends Model
{
    use HasFactory;

    protected $table = 'pedidos';
    protected $primaryKey = 'id_pedido';
    public $incrementing = true;
    protected $keyType = 'int';

        public function carro(): HasMany
    {
        return $this->hasMany(Carro::class, 'id_detalle', 'id_detalle');
    }
        public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
        public function credito(): HasMany
    {
        return $this->hasMany(Credito::class, 'id_credito', 'id_credito');
    }
    
}
