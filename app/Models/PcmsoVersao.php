<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Versão do PCMSO de um programa (`pcmsos`). Uma vigente por programa.
 *
 * @property int $id
 * @property int $pcmso_id
 * @property int $empresa_id
 * @property int $versao
 * @property string $status
 */
class PcmsoVersao extends Model
{
    use SoftDeletes;

    public const TABELA = 'pcmsos_versoes';

    public const STATUS_RASCUNHO = 'rascunho';

    public const STATUS_VIGENTE = 'vigente';

    public const STATUS_ARQUIVADO = 'arquivado';

    protected $table = self::TABELA;

    protected $fillable = [
        'pcmso_id',
        'empresa_id',
        'versao',
        'status',
        'vigencia_inicio',
        'vigencia_fim',
        'saude_medico_id',
        'observacao',
        'user_id',
        'publicado_em',
    ];

    protected $casts = [
        'id' => 'int',
        'pcmso_id' => 'int',
        'empresa_id' => 'int',
        'versao' => 'int',
        'status' => 'string',
        'vigencia_inicio' => 'date',
        'vigencia_fim' => 'date',
        'saude_medico_id' => 'int',
        'observacao' => 'string',
        'user_id' => 'int',
        'publicado_em' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $appends = ['vigencia_inicio_br', 'vigencia_fim_br', 'publicado_em_br'];

    public function getVigenciaInicioBrAttribute(): ?string
    {
        return $this->vigencia_inicio?->format('d/m/Y');
    }

    public function getVigenciaFimBrAttribute(): ?string
    {
        return $this->vigencia_fim?->format('d/m/Y');
    }

    public function getPublicadoEmBrAttribute(): ?string
    {
        return $this->publicado_em?->format('d/m/Y H:i');
    }

    public function Programa()
    {
        return $this->belongsTo(Pcmso::class, 'pcmso_id');
    }

    public function Coordenador()
    {
        return $this->belongsTo(SaudeMedico::class, 'saude_medico_id');
    }

    public function Exames()
    {
        return $this->hasMany(PcmsoVersaoExame::class, 'pcmso_versao_id', 'id');
    }
}
