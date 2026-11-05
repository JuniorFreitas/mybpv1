<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Cargo (vagas.id) dentro de uma versão do PGR. ausencia_risco = avaliado, sem fator de risco.
 */
class PgrInventarioCargo extends Model
{
    public const TABELA = 'pgr_inventarios_cargos';

    protected $table = self::TABELA;

    protected $fillable = [
        'pgr_inventario_id',
        'vaga_id',
        'ausencia_risco',
        'observacao',
    ];

    protected $casts = [
        'id' => 'int',
        'pgr_inventario_id' => 'int',
        'vaga_id' => 'int',
        'ausencia_risco' => 'boolean',
        'observacao' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function Riscos()
    {
        return $this->hasMany(PgrInventarioRisco::class, 'pgr_inventario_cargo_id', 'id');
    }
}
