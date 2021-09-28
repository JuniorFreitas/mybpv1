<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\AvaliacaoAnualFeedback
 *
 * @property int $feedback_id
 * @property int $pergunta_id
 * @property int $nota
 * @property int $quantidade_avaliacao
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Activitylog\Models\Activity[] $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AvaliacaoAnualFeedback newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AvaliacaoAnualFeedback newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AvaliacaoAnualFeedback query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AvaliacaoAnualFeedback whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AvaliacaoAnualFeedback whereFeedbackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AvaliacaoAnualFeedback whereNota($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AvaliacaoAnualFeedback wherePerguntaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AvaliacaoAnualFeedback whereQuantidadeAvaliacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AvaliacaoAnualFeedback whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int $id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\AvaliacaoAnualFeedback[] $AvaliacaoQuantidade
 * @property-read int|null $avaliacao_quantidade_count
 * @property-read \App\Models\FeedbackCurriculo $Feedback
 * @property-read \App\Models\FormularioAvaliacaoAnual $Pergunta
 * @property-read \App\Models\Topicos $Topicos
 * @property-read \App\Models\User $User
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AvaliacaoAnualFeedback whereId($value)
 * @property-read User|null $Usuario
 */
class AvaliacaoAnualFeedback extends Model
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

    protected $fillable = [
        'feedback_id',
        'pergunta_id',
        'nota',
        'quantidade_avaliacao'
    ];

    protected $casts = [
        'feedback_id' => 'int',
        'pergunta_id' => 'int',
        'nota' => 'int',
        'quantidade_avaliacao' => 'int'
    ];

    protected $table = 'avaliacao_anual_feedbacks';

    public function Pergunta()
    {
        return $this->hasOne(FormularioAvaliacaoAnual::class,'id','pergunta_id');
    }

    public function Topicos()
    {
        return $this->hasOne(Topicos::class, 'id', 'topicos_id');
    }

    public function Feedback()
    {
        return $this->hasOne(FeedbackCurriculo::class, 'id', 'feedback_id');
    }

    public function AvaliacaoQuantidade()
    {
        return $this->hasMany(AvaliacaoAnualFeedback::class, 'feedback_id', 'feedback_id');
    }

    public function Usuario()
    {
        return $this->hasOne(User::class, 'id', 'gestor_id');
    }
}
