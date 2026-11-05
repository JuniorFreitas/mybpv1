<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;

class EmpresaExameTipoPeriodicidadeHistorico extends Model
{
    use TenantTrait;

    public const TABELA = 'empresas_exame_tipos_periodicidades_historicos';

    protected $table = self::TABELA;

    protected $fillable = [
        'empresa_id',
        'user_id',
        'modo_aplicacao',
        'data_aplicacao',
        'job_despachado',
        'alteracoes',
        'descricao',
    ];

    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'user_id' => 'int',
        'modo_aplicacao' => 'string',
        'data_aplicacao' => 'string',
        'job_despachado' => 'boolean',
        'alteracoes' => 'array',
        'descricao' => 'string',
    ];

    protected $appends = [
        'data_aplicacao_br',
        'created_at_br',
    ];

    public function setDataAplicacaoAttribute($value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['data_aplicacao'] = null;

            return;
        }

        $this->attributes['data_aplicacao'] = (new DataHora($value))->dataInsert();
    }

    public function getDataAplicacaoBrAttribute(): ?string
    {
        if (empty($this->attributes['data_aplicacao'] ?? null)) {
            return null;
        }

        return (new DataHora($this->attributes['data_aplicacao']))->dataCompleta();
    }

    public function getCreatedAtBrAttribute(): ?string
    {
        if (!$this->created_at) {
            return null;
        }

        return $this->created_at->format('d/m/Y H:i');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
