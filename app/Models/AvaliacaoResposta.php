<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\AvaliacaoResposta
 *
 * @property int $id
 * @property int $empresa_id
 * @property int|null $avaliacao_feedback_id
 * @property int|null $topico_id
 * @property int $nota
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoResposta newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoResposta newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoResposta query()
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoResposta whereAvaliacaoFeedbackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoResposta whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoResposta whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoResposta whereNota($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoResposta whereTopicoId($value)
 * @mixin \Eloquent
 */
class AvaliacaoResposta extends Model
{
    use HasFactory, TenantTrait, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'avaliacoes_respostas';
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


    protected $table = "avaliacoes_respostas";

    protected $fillable = [
        'empresa_id',
        'avaliacao_feedback_id',
        'topico_id',
        'nota'
    ];

    protected $casts = [
        'id' => 'int',
        'topico_id' => 'int',
        'avaliacao_feedback_id' => 'int',
        'empresa_id' => 'int',
        'nota' => 'int'
    ];

    public $timestamps = false;

}
