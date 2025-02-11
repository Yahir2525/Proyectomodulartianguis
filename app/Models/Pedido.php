<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pedido extends Model
{
    use HasFactory;

        public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class);
    }
        public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }
    
        public function producto(): HasMany
    {
        return $this->hasMany(Producto::class);
    }
}
