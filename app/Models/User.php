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
        $hoy = Carbon::now();

        $creditosVencidos = $this->creditos()
            ->where('estado', 1)
            ->where('fecha_vencimiento', '<', $hoy)
            ->where('saldo_total', '>', 0)
            ->get();

        foreach ($creditosVencidos as $credito) {
            $totalAbonado = $credito->abonos()->sum('monto_abono');

            if ($totalAbonado < $credito->saldo_total) {
                return true;
            }
        }

        return false;
    }

    public function pagaSiempreAdelantado(): bool
    {
        // Trae todos los créditos con sus abonos (activos o históricos)
        $creditos = $this->creditos()->with('abonos')->get();

        // Sólo consideramos créditos que realmente tengan abonos y fecha de vencimiento
        $creditosConAbonos = $creditos->filter(function ($c) {
            return $c->abonos->isNotEmpty() && !empty($c->fecha_vencimiento);
        });

        $total = $creditosConAbonos->count();
        if ($total < 3) {
            return false;
        }

        $adelantados = 0;

        foreach ($creditosConAbonos as $credito) {
            $ultimoAbono = $credito->abonos->sortByDesc('created_at')->first();
            if (!$ultimoAbono) {
                continue;
            }

            $vence = $credito->fecha_vencimiento instanceof \Illuminate\Support\Carbon
                ? $credito->fecha_vencimiento->copy()
                : \Illuminate\Support\Carbon::parse($credito->fecha_vencimiento);

            // Cuenta como "adelantado" si el último abono fue >= 10 días ANTES del vencimiento
            if ($ultimoAbono->created_at < $vence->copy()->subDays(10)) {
                $adelantados++;
            }
        }

        // Mantén el umbral del 70%
        return $total > 0 && ($adelantados / $total) >= 0.7;
    }


    public function pagaTardePeroPaga(): bool
    {
        // Misma lógica original: último abono después del vencimiento y saldo_total == 0.
        $creditos = $this->creditosActivos()->with('abonos')->get();
        $cumple = 0;
        $total = 0;

        foreach ($creditos as $credito) {
            $total++;

            $ultimoAbono = $credito->abonos->sortByDesc('created_at')->first();

            if ($ultimoAbono && $credito->fecha_vencimiento) {
                $vence = $credito->fecha_vencimiento instanceof Carbon
                    ? $credito->fecha_vencimiento->copy()
                    : Carbon::parse($credito->fecha_vencimiento);

                if ($ultimoAbono->created_at > $vence && (float)$credito->saldo_total == 0.0) {
                    $cumple++;
                }
            }
        }

        return $total > 0 && ($cumple / $total) >= 0.7;
    }

    public function montoPromedio(): float
    {
        // Si ya tienes creditosActivos(), úsalo; si no, cambia por $this->creditos()
        $creditos = $this->creditosActivos()
            ->withSum('abonos', 'monto_abono')   // evita N+1
            ->get();

        if ($creditos->isEmpty()) return 0.0;

        // promedio de (saldo_total + total_abonado) por crédito
        return (float) $creditos->avg(function ($c) {
            $abonado = (float) ($c->abonos_sum_monto_abono ?? 0);
            return (float) $c->saldo_total + $abonado;
        });
    }

    public function estaBloqueadoParaCredito(): bool
    {
        return $this->tienePagosAtrasadosSinAbonar();
    }

    public function evaluarNivelUsuario(): void
    {
        // Misma estructura y decisiones que tenías.
        if ($this->tienePagosAtrasadosSinAbonar()) {
            $this->nivel_usuario = 'malo';
            $this->dias_aplazo   = 0;
        } elseif ($this->pagaSiempreAdelantado()) {
            $this->nivel_usuario = 'excelente';
            $this->dias_aplazo   = 1;
        } else {
            $this->nivel_usuario = 'bueno';
            $this->dias_aplazo   = 0;
        }

        $this->save();
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
