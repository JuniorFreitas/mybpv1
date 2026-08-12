<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtaReuniaoEvento extends Model
{
    public $timestamps = false;

    protected $table = 'ata_reuniao_eventos';

    protected $fillable = [
        'empresa_id',
        'ata_reuniao_id',
        'ator_id',
        'ator_tipo',
        'tipo_evento',
        'entidade_tipo',
        'entidade_id',
        'dados',
        'created_at',
    ];

    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'ata_reuniao_id' => 'int',
        'ator_id' => 'int',
        'ator_tipo' => 'string',
        'tipo_evento' => 'string',
        'entidade_tipo' => 'string',
        'entidade_id' => 'int',
        'dados' => 'array',
        'created_at' => 'datetime',
    ];
}
