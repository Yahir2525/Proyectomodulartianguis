<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Credito extends Model
{
    use HasFactory;
    
        public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'nombre_usuario', 'nombre_usuario');
    }

        public function compra(): HasMany
    {
        return $this->hasMany(Compra::class);
    }

    public function abono(): HasMany
    {
        return $this->hasMany(Abono::class);
    }
}
