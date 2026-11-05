<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Risco (exame_riscos) a que um cargo está exposto em uma versão do PGR.
 */
class PgrInventarioRisco extends Model
{
    public const TABELA = 'pgr_inventarios_riscos';

    protected $table = self::TABELA;

    protected $fillable = [
        'pgr_inventario_cargo_id',
        'exame_risco_id',
        'fonte_geradora',
        'intensidade',
        'medidas_controle',
    ];

    protected $casts = [
        'id' => 'int',
        'pgr_inventario_cargo_id' => 'int',
        'exame_risco_id' => 'int',
        'fonte_geradora' => 'string',
        'intensidade' => 'string',
        'medidas_controle' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
