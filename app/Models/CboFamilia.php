<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CboFamilia extends Model
{
    protected $table = 'cbo_familias';

    protected $fillable = [
        'codigo',
        'titulo',
        'descricao_sumaria',
        'fonte',
        'ativo',
        'data_importacao',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'data_importacao' => 'datetime',
    ];

    public function cbos(): HasMany
    {
        return $this->hasMany(Cbo::class, 'codigo_familia', 'codigo');
    }
}
