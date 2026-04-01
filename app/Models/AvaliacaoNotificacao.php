<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AvaliacaoNotificacao extends Model
{
    protected $table = 'avaliacoes_notificacoes';

    protected $fillable = [
        'empresa_id',
        'avaliacao_id',
        'avaliacao_feedback_id',
        'funcionario_id',
        'avaliador_id',
        'usuario_solicitante_id',
        'canal',
        'modo_disparo',
        'tipo',
        'status',
        'destinatario_nome',
        'destinatario_email',
        'destinatario_telefone',
        'assunto',
        'payload',
        'erro',
        'enviado_em',
    ];

    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'avaliacao_id' => 'int',
        'avaliacao_feedback_id' => 'int',
        'funcionario_id' => 'int',
        'avaliador_id' => 'int',
        'usuario_solicitante_id' => 'int',
        'payload' => 'array',
        'enviado_em' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
