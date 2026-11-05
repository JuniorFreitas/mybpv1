<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Exame complementar exigido para um cargo numa versão do PCMSO, por tipo de ASO.
 * unidade/valor = periodicidade própria do exame (opcional, ex.: audiometria anual).
 */
class PcmsoVersaoExame extends Model
{
    public const TABELA = 'pcmsos_versoes_exames';

    public const ORIGEM_MANUAL = 'manual';

    public const ORIGEM_SUGERIDO = 'sugerido';

    protected $table = self::TABELA;

    protected $fillable = [
        'pcmso_versao_id',
        'vaga_id',
        'exame_id',
        'exame_tipo_ids',
        'obrigatorio',
        'unidade',
        'valor',
        'origem',
        'observacao',
    ];

    protected $casts = [
        'id' => 'int',
        'pcmso_versao_id' => 'int',
        'vaga_id' => 'int',
        'exame_id' => 'int',
        'exame_tipo_ids' => 'array',
        'obrigatorio' => 'boolean',
        'unidade' => 'string',
        'valor' => 'int',
        'origem' => 'string',
        'observacao' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
