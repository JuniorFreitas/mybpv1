<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;

/**
 * App\Models\AvaliacaoNoventaFeedback
 *
 * @property int $id
 * @property int $feedback_id
 * @property int $pergunta_id
 * @property int $gestor_id usuário em sessãos
 * @property int $nota
 * @property int $quantidade_avaliacao
 * @property string $gestor_imediato
 * @property string|null $observacao
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AvaliacaoNoventaFeedbackQuantidade> $AvaliacaoQuantidade
 * @property-read int|null $avaliacao_quantidade_count
 * @property-read \App\Models\FeedbackCurriculo|null $Feedback
 * @property-read \App\Models\AvaliacaoNoventaDias|null $Pergunta
 * @property-read User|null $Usuario
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaFeedback newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaFeedback newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaFeedback query()
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaFeedback whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaFeedback whereFeedbackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaFeedback whereGestorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaFeedback whereGestorImediato($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaFeedback whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaFeedback whereNota($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaFeedback whereObservacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaFeedback wherePerguntaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaFeedback whereQuantidadeAvaliacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaFeedback whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class AvaliacaoNoventaFeedback extends Model
{
    use LogsActivity, HasActivitylogOptions;

    protected static $logFillable = true;
    protected static $logName = 'AvaliacaoNoventaFeedback';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    public function getDescriptionForEvent(string $eventName)
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
        'gestor_id',
        'nota',
        'quantidade_avaliacao',
        'observacao',
        'gestor_imediato'
    ];
    protected $casts = [
        'feedback_id' => 'int',
        'pergunta_id' => 'int',
        'gestor_id' => 'int',
        'nota' => 'int',
        'quantidade_avaliacao' => 'int',
        'gestor_imediato' => 'string'
    ];
    protected $table = 'avaliacao_noventa_feedbacks';


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

    public function Pergunta()
    {
        return $this->hasOne(AvaliacaoNoventaDias::class, 'id', 'pergunta_id');
    }

    public function Feedback()
    {
        return $this->hasOne(FeedbackCurriculo::class, 'id', 'feedback_id');
    }

    public function AvaliacaoQuantidade()
    {
        return $this->hasMany(AvaliacaoNoventaFeedbackQuantidade::class, 'feedback_id', 'feedback_id');
    }

    public function Usuario()
    {
        return $this->hasOne(User::class, 'id', 'gestor_id');
    }
}
