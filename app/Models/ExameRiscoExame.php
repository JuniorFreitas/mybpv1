<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Exame sugerido para quem está exposto a um risco, por tipo de ASO.
 */
class ExameRiscoExame extends Model
{
    public const TABELA = 'exame_riscos_exames';

    protected $table = self::TABELA;

    protected $fillable = [
        'exame_risco_id',
        'exame_id',
        'exame_tipo_ids',
    ];

    protected $casts = [
        'id' => 'int',
        'exame_risco_id' => 'int',
        'exame_id' => 'int',
        'exame_tipo_ids' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
