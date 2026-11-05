<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Histórico das Regras da empresa (quem, quando, o quê, modo de aplicação).
 */
class SaudeRegraHistorico extends Model
{
    public const TABELA = 'saude_regras_historicos';

    public const ACAO_CRIAR = 'criar';

    public const ACAO_EDITAR = 'editar';

    public const ACAO_ATIVAR = 'ativar';

    public const ACAO_DESATIVAR = 'desativar';

    public const ACAO_EXCLUIR = 'excluir';

    protected $table = self::TABELA;

    protected $fillable = [
        'empresa_id',
        'saude_regra_id',
        'user_id',
        'acao',
        'modo_aplicacao',
        'data_aplicacao',
        'job_despachado',
        'antes',
        'depois',
        'descricao',
    ];

    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'saude_regra_id' => 'int',
        'user_id' => 'int',
        'acao' => 'string',
        'modo_aplicacao' => 'string',
        'data_aplicacao' => 'date',
        'job_despachado' => 'boolean',
        'antes' => 'array',
        'depois' => 'array',
        'descricao' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = ['created_at_br', 'data_aplicacao_br'];

    public function getCreatedAtBrAttribute(): ?string
    {
        return $this->created_at?->format('d/m/Y H:i');
    }

    public function getDataAplicacaoBrAttribute(): ?string
    {
        return $this->data_aplicacao?->format('d/m/Y');
    }

    public function Usuario()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
