<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Carbon;

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

    public function pagaSiempreAdelantado()
    {
        $creditos = $this->creditosActivos()->with('abonos')->get();
        $adelantados = 0;
        $total = 0;

        foreach ($creditos as $credito) {
            $total++;
            $ultimoAbono = $credito->abonos()->orderByDesc('created_at')->first();
            if ($ultimoAbono && $ultimoAbono->created_at < $credito->fecha_vencimiento->subDays(10)) {
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
            $ultimoAbono = $credito->abonos()->orderByDesc('created_at')->first();
            if ($ultimoAbono && $ultimoAbono->created_at > $credito->fecha_vencimiento && $credito->saldo_total == 0) {
                $cumple++;
            }
        }

        return $total > 0 && $cumple / $total >= 0.7;
    }

    public function montoPromedio()
    {
        $creditos = $this->creditosActivos()->get();
        if ($creditos->isEmpty()) return 0;

        $suma = $creditos->sum('monto_original');
        return $suma / $creditos->count();
    }

    public function estaBloqueadoParaCredito()
    {
        return $this->tienePagosAtrasadosSinAbonar();
    }

    public function evaluarNivelUsuario()
    {
        if ($this->tienePagosAtrasadosSinAbonar()) {
            $this->nivel_usuario = 'malo';
            $this->dias_aplazo = 0;
        } elseif ($this->pagaSiempreAdelantado()) {
            $this->nivel_usuario = 'excelente';
            $this->dias_aplazo = 1;
        } else {
            $this->nivel_usuario = 'bueno';
            $this->dias_aplazo = 0;
        }
        $this->save();
    }

}
