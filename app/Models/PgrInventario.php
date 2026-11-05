<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Versão do inventário de riscos do PGR (NR-1). Só uma vigente por empresa.
 *
 * @property int $id
 * @property int $empresa_id
 * @property int $versao
 * @property string $status
 */
class PgrInventario extends Model
{
    use SoftDeletes;

    public const TABELA = 'pgr_inventarios';

    public const STATUS_RASCUNHO = 'rascunho';

    public const STATUS_VIGENTE = 'vigente';

    public const STATUS_ARQUIVADO = 'arquivado';

    protected $table = self::TABELA;

    protected $fillable = [
        'empresa_id',
        'versao',
        'status',
        'vigencia_inicio',
        'vigencia_fim',
        'responsavel_tecnico',
        'responsavel_registro',
        'observacao',
        'user_id',
        'publicado_em',
    ];

    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'versao' => 'int',
        'status' => 'string',
        'vigencia_inicio' => 'date',
        'vigencia_fim' => 'date',
        'responsavel_tecnico' => 'string',
        'responsavel_registro' => 'string',
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

    public function Cargos()
    {
        return $this->hasMany(PgrInventarioCargo::class, 'pgr_inventario_id', 'id');
    }
}
