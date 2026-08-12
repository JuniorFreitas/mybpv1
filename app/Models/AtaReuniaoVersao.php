<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Model;

class AtaReuniaoVersao extends Model
{
    use TenantTrait;

    protected $table = 'ata_reuniao_versoes';

    protected $fillable = [
        'empresa_id',
        'ata_reuniao_id',
        'numero',
        'autor_id',
        'descricao',
        'campos_alterados',
        'snapshot',
    ];

    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'ata_reuniao_id' => 'int',
        'numero' => 'string',
        'autor_id' => 'int',
        'descricao' => 'string',
        'campos_alterados' => 'array',
        'snapshot' => 'array',
    ];
}
