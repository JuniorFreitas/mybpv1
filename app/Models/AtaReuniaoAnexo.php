<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AtaReuniaoAnexo extends Model
{
    use SoftDeletes, TenantTrait;

    protected $table = 'ata_reuniao_anexos';

    protected $fillable = [
        'empresa_id',
        'ata_reuniao_id',
        'ata_reuniao_acao_id',
        'arquivo_id',
        'usuario_id',
        'nome',
        'tipo',
        'tamanho',
        'link',
        'secao',
    ];

    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'ata_reuniao_id' => 'int',
        'ata_reuniao_acao_id' => 'int',
        'arquivo_id' => 'int',
        'usuario_id' => 'int',
        'nome' => 'string',
        'tipo' => 'string',
        'tamanho' => 'int',
        'link' => 'string',
        'secao' => 'string',
    ];
}
