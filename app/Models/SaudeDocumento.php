<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Documento SST (Saúde Ocupacional). O arquivo e a vigência ficam nas versões.
 *
 * @property int $id
 * @property int $empresa_id
 * @property string $tipo
 * @property string $titulo
 * @property string $sigilo
 */
class SaudeDocumento extends Model
{
    use SoftDeletes;

    public const TABELA = 'saude_documentos';

    public const SIGILO_COMUM = 'comum';

    public const SIGILO_SENSIVEL = 'sensivel';

    /** tipo => [rótulo, sigilo padrão] */
    public const TIPOS = [
        'pcmso' => ['PCMSO', self::SIGILO_COMUM],
        'pgr' => ['PGR', self::SIGILO_COMUM],
        'relatorio_analitico' => ['Relatório analítico do PCMSO', self::SIGILO_COMUM],
        'ltcat' => ['LTCAT', self::SIGILO_COMUM],
        'laudo_insalubridade' => ['Laudo de insalubridade', self::SIGILO_COMUM],
        'laudo_periculosidade' => ['Laudo de periculosidade', self::SIGILO_COMUM],
        'ordem_servico' => ['Ordem de serviço (NR-1)', self::SIGILO_COMUM],
        'certificado' => ['Certificado / treinamento', self::SIGILO_COMUM],
        'aso' => ['ASO', self::SIGILO_COMUM],
        'laudo_medico' => ['Laudo / exame médico', self::SIGILO_SENSIVEL],
        'outro' => ['Outro', self::SIGILO_COMUM],
    ];

    protected $table = self::TABELA;

    protected $fillable = [
        'empresa_id',
        'tipo',
        'titulo',
        'sigilo',
        'feedback_id',
        'origem_tipo',
        'origem_id',
        'responsavel',
        'observacao',
        'user_id',
    ];

    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'tipo' => 'string',
        'titulo' => 'string',
        'sigilo' => 'string',
        'feedback_id' => 'int',
        'origem_tipo' => 'string',
        'origem_id' => 'int',
        'responsavel' => 'string',
        'observacao' => 'string',
        'user_id' => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function Versoes()
    {
        return $this->hasMany(SaudeDocumentoVersao::class, 'saude_documento_id', 'id');
    }
}
