<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cbo extends Model
{
    protected $table = 'cbos';

    protected $fillable = [
        'codigo',
        'titulo',
        'codigo_familia',
        'fonte',
        'ativo',
        'data_importacao',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'data_importacao' => 'datetime',
    ];

    public function familia(): BelongsTo
    {
        return $this->belongsTo(CboFamilia::class, 'codigo_familia', 'codigo');
    }
}
