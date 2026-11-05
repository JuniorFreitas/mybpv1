<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Médico responsável (Saúde Ocupacional): coordenador do PCMSO ou examinador.
 *
 * @property int $id
 * @property int $empresa_id
 * @property string $nome
 * @property string $crm
 * @property string $crm_uf
 * @property string|null $rqe
 * @property bool $ativo
 */
class SaudeMedico extends Model
{
    use SoftDeletes;

    public const TABELA = 'saude_medicos';

    protected $table = self::TABELA;

    protected $fillable = [
        'empresa_id',
        'nome',
        'crm',
        'crm_uf',
        'rqe',
        'especialidade',
        'email',
        'telefone',
        'ativo',
        'user_id',
    ];

    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'nome' => 'string',
        'crm' => 'string',
        'crm_uf' => 'string',
        'rqe' => 'string',
        'especialidade' => 'string',
        'email' => 'string',
        'telefone' => 'string',
        'ativo' => 'boolean',
        'user_id' => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function getCrmCompletoAttribute(): string
    {
        return 'CRM ' . $this->crm . '/' . $this->crm_uf;
    }

    public function getUpdatedAtBrAttribute(): ?string
    {
        return $this->updated_at?->format('d/m/Y H:i');
    }

    public function Vinculos()
    {
        return $this->hasMany(SaudeMedicoVinculo::class, 'saude_medico_id', 'id');
    }
}
