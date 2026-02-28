<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssinaturaCotaAlertaEnvio extends Model
{
    use HasFactory;

    protected $table = 'assinatura_cota_alerta_envios';

    protected $fillable = [
        'empresa_id',
        'competencia',
        'percentual',
        'usadas',
        'limite',
    ];

    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'percentual' => 'int',
        'usadas' => 'int',
        'limite' => 'int',
    ];
}

