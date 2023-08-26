<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\AvaliacaoAnualFeedbackQuantidade
 *
 * @property int $id
 * @property int $feedback_id
 * @property int $quantidade_avaliacao
 * @property int $gestor_id
 * @property string $gestor_imediato
 * @property string|null $observacao
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\FeedbackCurriculo|null $Feedback
 * @property-read \App\Models\User|null $Usuario
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoAnualFeedbackQuantidade newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoAnualFeedbackQuantidade newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoAnualFeedbackQuantidade query()
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoAnualFeedbackQuantidade whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoAnualFeedbackQuantidade whereFeedbackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoAnualFeedbackQuantidade whereGestorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoAnualFeedbackQuantidade whereGestorImediato($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoAnualFeedbackQuantidade whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoAnualFeedbackQuantidade whereObservacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoAnualFeedbackQuantidade whereQuantidadeAvaliacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoAnualFeedbackQuantidade whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class AvaliacaoAnualFeedbackQuantidade extends Model
{
    use LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'area';
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

    //Acessor ->created_at
    public function getCreatedAtAttribute($value)
    {
        if ($value) {
            $data = new DataHora($this->attributes['created_at']);
            return $data->dataCompleta();
        }
    }

    //Modificador ->created_at
    public function setCreatedAtAttribute($value)
    {
        if ($value) {
            $data = new DataHora($value);
            $this->attributes['created_at'] = $data->dataInsert();
        }
    }

    protected $fillable = [
        'id',
        'feedback_id',
        'quantidade_avaliacao',
        'gestor_id',
        'gestor_imediato',
        'observacao'
    ];

    protected $casts = [
        'feedback_id' => 'int',
        'quantidade_avaliacao' => 'int',
        'gestor_id' => 'int',
        'gestor_imediato' => 'string',
    ];

    protected $table = 'avaliacao_anual_feedback_quantidades';

    public function Feedback()
    {
        return $this->hasOne(FeedbackCurriculo::class, 'id', 'feedback_id');
    }

    public function Usuario()
    {
        return $this->hasOne(User::class, 'id', 'gestor_id');
    }
}
