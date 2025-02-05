<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    use HasFactory;

    public function compra(): HasMany{

        return $this->hasMany(Compra::class);
    }

    public function credito(): HasMany{

        return $this->hasMany(Credito::class);
    }

    public function abono(): HasMany{

        return $this->HasMany(Abono::class);
    }
}
