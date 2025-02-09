<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Compra extends Model
{
    use HasFactory;

        public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'nombre_usuario', 'nombre_usuario');
    }
        public function credito(): BelongsTo
    {
        return $this->belongsTo(Credito::class);
    }
        public function pedido(): HasOne
    {
        return $this->hasOne(Pedido::class);
    }
}
