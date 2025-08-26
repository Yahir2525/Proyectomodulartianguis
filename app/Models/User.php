<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $table = 'users';
    protected $primaryKey = 'id_user';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'name',
        'nombre_usuario',
        'email',
        'password',
        'nivel_usuario',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ======= Relaciones =======

    public function pedido(): HasMany
    {
        return $this->hasMany(Pedido::class, 'id_user', 'id_user');
    }

    public function creditos(): HasMany
    {
        return $this->hasMany(Credito::class, 'id_user', 'id_user');
    }

    public function abono(): HasMany
    {
        return $this->hasMany(Abono::class, 'id_user', 'id_user');
    }

    public function carro(): HasMany
    {
        return $this->hasMany(Carro::class, 'id_user', 'id_user');
    }

    public function creditosActivos()
    {
        return $this->creditos()->where('estado', 1);
    }

    public function abonos()
    {
        return $this->hasManyThrough(Abono::class, Credito::class, 'id_user', 'id_credito', 'id_user', 'id_credito');
    }

    // ======= Comportamiento de crédito =======

    public function tienePagosAtrasadosSinAbonar()
    {
        $hoy = Carbon::now()->startOfDay();

        return $this->creditos()
            ->where('estado', 1)
            ->where('saldo_total', '>', 0)
            ->whereDate('fecha_vencimiento', '<', $hoy)
            ->exists();

    }

    public function estaBloqueadoParaCredito(): bool
    {
        return $this->tienePagosAtrasadosSinAbonar();
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
