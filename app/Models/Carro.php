<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;


class Carro extends Model
{
    use HasFactory;

    protected $table = 'carros';
    protected $primaryKey = 'id_carro';
    public $incrementing = true;
    protected $keyType = 'int';

    public function productos(): BelongsToMany
    {
        return $this->BelongsToMany(Producto::class, 'carros','id_carro', 'id_producto')->withPivot('cantidad');
    }
}
