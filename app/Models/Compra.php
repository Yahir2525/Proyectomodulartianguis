<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class Compra extends Model
{
    use HasFactory;

    protected $table = 'compras';
    protected $primaryKey = 'id_compra';
    public $incrementing = true;
    protected $keyType = 'int';

        public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nombre_usuario', 'nombre_usuario');
    }
        public function credito(): HasMany
    {
        return $this->hasMany(Credito::class, 'id_compra', 'id_compra');
    }
        public function pedido(): HasMany
    {
        return $this->hasMany(Pedido::class, 'id_compra', 'id_compra');
    }
    public function carro(): BelongsTo
    {
        return $this->belongsTo(Carro::class, 'id_compra', 'id_carro');
    }
}
