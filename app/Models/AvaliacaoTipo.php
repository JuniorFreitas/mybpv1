<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\AvaliacaoTipo
 *
 * @property int $id
 * @property string $nome
 * @property string $descricao
 * @property int $empresa_id
 * @property int $ativo
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Avaliacao[] $Avaliacoes
 * @property-read int|null $avaliacoes_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\AvaliacaoTopico[] $AvaliacoesTopicos
 * @property-read int|null $avaliacoes_topicos_count
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTipo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTipo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTipo query()
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTipo whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTipo whereDescricao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTipo whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTipo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTipo whereNome($value)
 * @mixin \Eloquent
 * @property-read \App\Models\AvaliacaoTopico $AvaliacaoTipo
 */
class AvaliacaoTipo extends Model
{
    use HasFactory, TenantTrait;

    protected $table = "avaliacoes_tipos";

    protected $fillable = [
        'titulo',
        'avaliacao_tipo_id',
        'data_inicio_prazo',
        'data_fim_prazo',
        'empresa_id',
        'status',
        'ativo'
    ];

    protected $casts = ['id' => 'int', 'titulo' => 'string', 'avaliacao_tipo_id' => 'int', 'empresa_id' => 'int', 'data_inicio_prazo' => 'datetime', 'data_fim_prazo' => 'datetime', 'status' => 'string', 'ativo' => 'boolean'];

    public $timestamps = false;

    public function AvaliacaoTipo()
    {
        return $this->belongsTo(AvaliacaoTopico::class, 'avaliacao_tipo_id', 'id');
    }
}
