<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';
    protected $primaryKey = 'id_producto';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = ['nombre', 'tipo', 'material', 'color', 'tamanio', 'marca', 'precio_unitario', 'piezas', 'imagen','estado_producto'];

    protected $appends = ['imagen_url'];
    
    public function pedidos(): BelongsTo
    {
        return $this->belongsToMany(Producto::class, 'pedidos', 'id_producto', 'id_pedido')->withPivot('cantidad');
    }

    public function carros(): BelongsToMany
    {
        return $this->belongsToMany(Carro::class, 'carro_productos', 'id_producto', 'id_carro')->withPivot('cantidad');
    }

    public function getImagenUrlAttribute(): ?string
    {
        if (!$this->imagen) {
            return null;
        }

        if (Str::startsWith($this->imagen, ['http://', 'https://'])) {
            return $this->imagen;
        }

        if (config('filesystems.disks.s3')) {
            return Storage::disk('s3')->url(ltrim($this->imagen, '/'));
        }

        return asset(ltrim($this->imagen, '/'));
    }
}
