<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\AvaliacaoTopico
 *
 * @property int $id
 * @property int $empresa_id
 * @property int|null $topico_pai_id
 * @property string $topico
 * @property string $topico_explicacao
 * @property int $ativo
 * @property-read \App\Models\AvaliacaoTipo $AvaliacoesTipos
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTopico newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTopico newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTopico query()
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTopico whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTopico whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTopico whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTopico whereTopico($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTopico whereTopicoExplicacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTopico whereTopicoPaiId($value)
 * @mixin \Eloquent
 * @property int|null $avaliacao_tipo_id
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTopico whereAvaliacaoTipoId($value)
 * @property-read \App\Models\AvaliacaoTipo|null $AvaliacaoTipo
 * @property-read \Illuminate\Database\Eloquent\Collection|AvaliacaoTopico[] $Subtopicos
 * @property-read int|null $subtopicos_count
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTopico topicosPais()
 */
class AvaliacaoTopico extends Model
{
    use HasFactory, TenantTrait;

    protected $table = "avaliacoes_topicos";

    protected $fillable = [
        'empresa_id',
        'avaliacao_tipo_id',
        'topico_pai_id',
        'topico',
        'topico_explicacao',
        'ativo'
    ];

    protected $casts = ['id' => 'int', 'avaliacao_tipo_id' => 'int', 'topico_pai_id' => 'string', 'topico' => 'string', 'topico_explicacao' => 'string', 'empresa_id' => 'int', 'ativo' => 'boolean'];

    public $timestamps = false;


    public function AvaliacaoTipo()
    {
        return $this->belongsTo(AvaliacaoTipo::class, 'avaliacao_tipo_id', 'id');
    }

    public function Subtopicos()
    {
        return $this->hasMany(AvaliacaoTopico::class, 'topico_pai_id', 'id');
    }

    public function scopeTopicosPais($query)
    {
        return $query->whereNull('topico_pai_id');
    }
}
