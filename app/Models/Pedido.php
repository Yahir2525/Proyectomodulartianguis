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

    protected $fillable = [
    'id_user',
    'id_credito',
    ];

        public function carro(): HasOne
    {
        return $this->hasOne(Carro::class, 'id_pedido', 'id_pedido');
    }
        public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
        public function credito(): BelongsTo
    {
        return $this->belongsTo(Credito::class, 'id_credito', 'id_credito');
    }
}
