<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;

/**
 * App\Models\AvaliacaoFeedback
 *
 * @property int $id
 * @property int|null $avaliacao_id
 * @property int $empresa_id
 * @property string $origem_feedback
 * @property bool $principal
 * @property int|null $avaliador_id
 * @property int|null $funcionario_id
 * @property int|null $nota_final_total
 * @property string|null $inicio_feedback
 * @property string|null $fim_feedback
 * @property string|null $comentario
 * @property string $status
 * @property string|null $estado_atual
 * @property string|null $estado_desejado
 * @property-read \App\Models\Avaliacao|null $Avaliacao
 * @property-read \App\Models\User|null $Avaliador
 * @property-read \App\Models\User|null $Funcionario
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AvaliacaoResposta> $Respostas
 * @property-read int|null $respostas_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoFeedback newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoFeedback newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoFeedback origemAvaliador()
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoFeedback query()
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoFeedback whereAvaliacaoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoFeedback whereAvaliadorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoFeedback whereComentario($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoFeedback whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoFeedback whereEstadoAtual($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoFeedback whereEstadoDesejado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoFeedback whereFimFeedback($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoFeedback whereFuncionarioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoFeedback whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoFeedback whereInicioFeedback($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoFeedback whereNotaFinalTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoFeedback whereOrigemFeedback($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoFeedback wherePrincipal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoFeedback whereStatus($value)
 * @property int|null $avaliacao_tipo_id
 * @property-read \App\Models\AvaliacaoAvaliadoresTipos|null $TipoAvaliador
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoFeedback whereAvaliacaoTipoId($value)
 * @property bool $tipo_pj
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoFeedback whereTipoPj($value)
 * @mixin \Eloquent
 */
class AvaliacaoFeedback extends Model
{
    use TenantTrait, LogsActivity, HasActivitylogOptions;

    protected static $logFillable = true;
    protected static $logName = 'avaliacoes_feedbacks';
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


    protected $table = 'avaliacoes_feedbacks';

    protected $fillable = [
        'avaliacao_id',
        'empresa_id',
        'funcionario_id',
        'feedback_id',
        'avaliacao_tipo_id',
        'origem_feedback',
        'principal',
        'avaliador_id',
        'nota_final_total',
        'inicio_feedback',
        'fim_feedback',
        'comentario',
        'status',
        'estado_atual',
        'estado_desejado',
        'tipo_pj'
    ];

    protected $casts = [
        'id' => 'int',
        'avaliacao_id' => 'int',
        'empresa_id' => 'int',
        'feedback_id' => 'int',
        'avaliacao_tipo_id' => 'int',
        'funcionario_id' => 'int',
        'principal' => 'boolean',
        'origem_feedback' => 'string',
        'avaliador_id' => 'int',
        'nota_final_total' => 'int',
        'inicio_feedback' => 'string',
        'fim_feedback' => 'string',
        'comentario' => 'string',
        'status' => 'string',
        'estado_atual' => 'string',
        'estado_desejado' => 'string',
        'tipo_pj' => 'boolean'
    ];

    public $timestamps = false;

    const ORIGEM_FUNCIONARIO = 'Funcionario';
    const ORIGEM_AVALIADOR = 'Avaliador';

    const LISTA_ORIGEM = [
        self::ORIGEM_FUNCIONARIO,
        self::ORIGEM_AVALIADOR
    ];

    const STATUS_AGUARDANDO = 'Pendente';
    const STATUS_CONCLUIDA = 'Avaliada';
    const STATUS_FINAL = 'Finalizada';

    const LISTA_STATUS = [
        self::STATUS_AGUARDANDO,
        self::STATUS_CONCLUIDA,
        self::STATUS_FINAL
    ];

    public function TipoAvaliador()
    {
        return $this->hasOne(AvaliacaoAvaliadoresTipos::class, 'id', 'avaliacao_tipo_id');
    }

    /**
     * @return HasOne
     */
    public function Avaliador()
    {
        return $this->hasOne(User::class, 'id', 'avaliador_id');
    }

    /**
     * @return BelongsTo
     */
    public function Avaliacao()
    {
        return $this->belongsTo(Avaliacao::class, 'avaliacao_id', 'id');
    }

    /**
     * @return HasOne
     */
    public function Funcionario()
    {
        return $this->hasOne(User::class, 'id', 'funcionario_id');
    }

    /**
     * @return HasMany
     */
    public function Respostas()
    {
        return $this->hasMany(AvaliacaoResposta::class, 'avaliacao_feedback_id', 'id');
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeOrigemAvaliador($query)
    {
        return $query->where('origem_feedback', AvaliacaoFeedback::ORIGEM_AVALIADOR);
    }

    //criar um observer para atualizar definir se é tipo_pj ou não vindo da avaliacao_id
    protected static function booted()
    {
        static::creating(function ($model) {
            $avaliacao = Avaliacao::find($model->avaliacao_id);
            $model->setAttribute('tipo_pj', $avaliacao->tipo_pj);
        });

        static::updating(function ($model) {
            $avaliacao = Avaliacao::find($model->avaliacao_id);
            $model->setAttribute('tipo_pj', $avaliacao->tipo_pj);
        });
    }
}
