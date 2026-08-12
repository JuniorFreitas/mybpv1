<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtaReuniaoNotificacao extends Model
{
    protected $table = 'ata_reuniao_notificacoes';

    protected $fillable = [
        'empresa_id',
        'ata_reuniao_id',
        'ata_reuniao_acao_id',
        'destinatario_id',
        'canal',
        'tipo',
        'modo_disparo',
        'status',
        'data_prazo_referencia',
        'destinatario_nome',
        'destinatario_email',
        'assunto',
        'payload',
        'erro',
        'enviado_em',
    ];

    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'ata_reuniao_id' => 'int',
        'ata_reuniao_acao_id' => 'int',
        'destinatario_id' => 'int',
        'canal' => 'string',
        'tipo' => 'string',
        'modo_disparo' => 'string',
        'status' => 'string',
        'data_prazo_referencia' => 'date',
        'destinatario_nome' => 'string',
        'destinatario_email' => 'string',
        'assunto' => 'string',
        'payload' => 'array',
        'erro' => 'string',
        'enviado_em' => 'datetime',
    ];
}
