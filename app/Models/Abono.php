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

    public function user(): BelongsTo
{
    return $this->belongsTo(User::class, 'id_user', 'id_user');
}

    public function credito(): BelongsTo
{
    return $this->belongsTo(Credito::class, 'id_credito', 'id_credito');
}

}
