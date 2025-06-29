<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\AvaliacaoTopico
 *
 * @property int $id
 * @property int $empresa_id
 * @property int|null $avaliacao_tipo_id
 * @property string|null $topico_pai_id
 * @property string $topico
 * @property string|null $topico_explicacao
 * @property bool $ativo
 * @property-read \App\Models\AvaliacaoTipo|null $AvaliacaoTipo
 * @property-read \Illuminate\Database\Eloquent\Collection<int, AvaliacaoTopico> $Subtopicos
 * @property-read int|null $subtopicos_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTopico newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTopico newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTopico query()
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTopico topicosPais()
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTopico whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTopico whereAvaliacaoTipoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTopico whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTopico whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTopico whereTopico($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTopico whereTopicoExplicacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoTopico whereTopicoPaiId($value)
 * @mixin \Eloquent
 */
class AvaliacaoTopico extends Model
{
    use HasFactory, TenantTrait, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'avaliacoes_topicos';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = "";
    }


    protected $table = "avaliacoes_topicos";

    protected $fillable = [
        'empresa_id',
        'avaliacao_tipo_id',
        'topico_pai_id',
        'topico',
        'topico_explicacao',
        'ativo',
        'tipo_pj'
    ];

    protected $casts = ['id' => 'int', 'avaliacao_tipo_id' => 'int', 'topico_pai_id' => 'string', 'topico' => 'string', 'topico_explicacao' => 'string', 'empresa_id' => 'int', 'ativo' => 'boolean', 'tipo_pj' => 'boolean'];

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
