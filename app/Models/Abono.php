<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Abono extends Model
{
    use HasFactory;

    protected $table = 'abonos';
    protected $primaryKey = 'id_abono';
    public $incrementing = true;
    protected $keyType = 'int';

    public function cliente(): BelongsTo
{
    return $this->belongsTo(Cliente::class, 'nombre_usuario', 'nombre_usuario');
}

    public function credito(): BelongsTo
{
    return $this->belongsTo(Credito::class, 'id_credito', 'id_credito');
}

}
