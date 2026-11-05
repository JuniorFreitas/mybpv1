<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Regra da empresa (Saúde Ocupacional): QUANDO condicoes ENTÃO periodicidade (unidade/valor).
 *
 * @property int $id
 * @property int $empresa_id
 * @property string $nome
 * @property string|null $modelo
 * @property list<int> $exame_tipo_ids
 * @property list<array{tipo: string, valor: mixed}> $condicoes
 * @property string $unidade
 * @property int $valor
 * @property bool $ativo
 * @property int|null $user_id
 */
class SaudeRegra extends Model
{
    use SoftDeletes;

    public const TABELA = 'saude_regras';

    protected $table = self::TABELA;

    protected $fillable = [
        'empresa_id',
        'nome',
        'modelo',
        'exame_tipo_ids',
        'condicoes',
        'unidade',
        'valor',
        'ativo',
        'user_id',
    ];

    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'nome' => 'string',
        'modelo' => 'string',
        'exame_tipo_ids' => 'array',
        'condicoes' => 'array',
        'unidade' => 'string',
        'valor' => 'int',
        'ativo' => 'boolean',
        'user_id' => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $appends = ['updated_at_br'];

    public function getUpdatedAtBrAttribute(): ?string
    {
        return $this->updated_at?->format('d/m/Y H:i');
    }

    public function getCreatedAtBrAttribute(): ?string
    {
        return $this->created_at?->format('d/m/Y H:i');
    }

    public function Usuario()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
