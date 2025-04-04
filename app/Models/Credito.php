<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Credito extends Model
{
    use HasFactory;

    protected $guarded = [];
    
    protected $table = 'creditos';
    protected $primaryKey = 'id_credito';
    public $incrementing = true;
    protected $keyType = 'int';


        public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'nombre_usuario', 'nombre_usuario');
    }

        public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class, 'id_compra', 'id_compra');
    }

    public function abono(): HasMany
    {
        return $this->hasMany(Abono::class, 'id_credito', 'id_credito');
    }

    // public function pedido(): HasMany
    // {
    //     return $this->hasMany(Pedido::class, 'id_credito', 'id_credito');
    // }
}
