<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Model;

class AtaReuniaoCiencia extends Model
{
    use TenantTrait;

    protected $table = 'ata_reuniao_ciencias';

    protected $fillable = [
        'empresa_id',
        'ata_reuniao_id',
        'user_id',
        'tipo',
        'ip',
        'comentario',
        'confirmado_em',
    ];

    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'ata_reuniao_id' => 'int',
        'user_id' => 'int',
        'tipo' => 'string',
        'ip' => 'string',
        'comentario' => 'string',
        'confirmado_em' => 'datetime',
    ];
}
