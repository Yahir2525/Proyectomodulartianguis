<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';
    protected $primaryKey = 'id_cliente';
    public $incrementing = true;
    protected $keyType = 'int';

        public function compra(): HasMany
    {
        return $this->hasMany(Compra::class, 'nombre_usuario', 'nombre_usuario');
    }

        public function credito(): HasOne
    {
        return $this->hasOne(Credito::class, 'nombre_usuario', 'nombre_usuario');
    }

        public function abono(): HasMany
    {
        return $this->hasMany(Abono::class, 'nombre_usuario', 'nombre_usuario');
    }

        public function pedido(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }
}
