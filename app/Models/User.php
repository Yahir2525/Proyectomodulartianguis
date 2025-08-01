<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Carbon;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    use HasRoles;

    protected $table = 'users';
    protected $primaryKey = 'id_user';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'name',
        'nombre_usuario',
        'email',
        'password',
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

    public function pedido(): HasMany
    {
        return $this->hasMany(Pedido::class, 'id_user', 'id_user');
    }

    public function credito(): HasMany
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

    // ====== FUNCIONES DE COMPORTAMIENTO DE PAGO ======

    public function creditosActivos()
    {
        return $this->hasMany(Credito::class, 'id_user')->where('estado', 1);
    }

    public function abonos()
    {
        return $this->hasManyThrough(Abono::class, Credito::class, 'id_user', 'id_credito', 'id_user', 'id_credito');
    }

    public function pagaSiempreAdelantado()
    {
        $creditos = $this->creditosActivos()->with('abonos')->get();
        $adelantados = 0;
        $total = 0;

        foreach ($creditos as $credito) {
            $total++;
            $ultimoAbono = $credito->abonos()->orderByDesc('fecha_abono')->first();
            if ($ultimoAbono && $ultimoAbono->fecha_abono < $credito->fecha_vencimiento->subDays(10)) {
                $adelantados++;
            }
        }

        return $total > 0 && $adelantados / $total >= 0.7;
    }

    public function pagaTardePeroPaga()
    {
        $creditos = $this->creditosActivos()->with('abonos')->get();
        $cumple = 0;
        $total = 0;

        foreach ($creditos as $credito) {
            $total++;
            $ultimoAbono = $credito->abonos()->orderByDesc('fecha_abono')->first();
            if ($ultimoAbono && $ultimoAbono->fecha_abono > $credito->fecha_vencimiento && $credito->saldo_total == 0) {
                $cumple++;
            }
        }

        return $total > 0 && $cumple / $total >= 0.7;
    }

    public function tienePagosAtrasadosSinAbonar()
    {
        $hoy = Carbon::now();
        return $this->creditosActivos()
            ->where('fecha_vencimiento', '<', $hoy)
            ->where('saldo_total', '>', 0)
            ->exists();
    }

    public function montoPromedio()
    {
        $creditos = $this->creditosActivos()->get();
        if ($creditos->isEmpty()) return 0;

        $suma = $creditos->sum('monto_original');
        return $suma / $creditos->count();
    }

    public function aumentarLimiteCredito()
    {
        // Esta función debe ajustarse si existe un campo 'limite_credito' en la base de datos
        if (isset($this->limite_credito)) {
            $this->limite_credito += 1000;
            $this->save();
        }
    }

    public function estaBloqueadoParaCredito()
    {
        return $this->tienePagosAtrasadosSinAbonar();
    }
}
