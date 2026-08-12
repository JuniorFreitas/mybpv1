<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtaReuniaoCompartilhamentoExterno extends Model
{
    protected $table = 'ata_reuniao_compartilhamentos_externos';

    protected $fillable = [
        'empresa_id',
        'ata_reuniao_id',
        'token_hash',
        'nome_externo',
        'email_externo',
        'escopo',
        'criado_por',
        'expira_em',
        'revogado_em',
        'ultimo_acesso_em',
    ];

    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'ata_reuniao_id' => 'int',
        'token_hash' => 'string',
        'nome_externo' => 'string',
        'email_externo' => 'string',
        'escopo' => 'string',
        'criado_por' => 'int',
        'expira_em' => 'datetime',
        'revogado_em' => 'datetime',
        'ultimo_acesso_em' => 'datetime',
    ];
}
