<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Carro extends Model
{
    use HasFactory;

    protected $table = 'carros';
    protected $primaryKey = 'id_carro';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'id_compra', // <- agrega esto si lo vas a llenar con update o create
    ];

    public function productos(): BelongsToMany
    {
        return $this->belongsToMany(Producto::class, 'carros','id_carro', 'id_producto')->withPivot('cantidad');
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'id_pedido', 'id_pedido');
    }
}
