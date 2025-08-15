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

        // 1) Si ya es URL absoluta (S3/CloudFront), úsala tal cual
        if (Str::startsWith($this->imagen, ['http://', 'https://'])) {
            return $this->imagen;
        }

        // 2) Si es ruta relativa, genera URL pública desde el disco S3 (sin prefirmar)
        //    Esto usará el 'url' configurado del disco s3 (ideal: tu dominio de CloudFront)
        if (config('filesystems.disks.s3')) {
            return Storage::disk('s3')->url(ltrim($this->imagen, '/'));
        }

        // 3) Fallback local (por si todavía tienes imágenes en public/)
        return asset(ltrim($this->imagen, '/'));
    }
}
