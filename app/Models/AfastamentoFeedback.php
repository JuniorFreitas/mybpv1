<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\AfastamentoFeedback
 *
 * @property-read \App\Models\FeedbackCurriculo $Feedback
 * @property-read \App\Models\User $Usuario
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Activitylog\Models\Activity[] $activities
 * @property-read int|null $activities_count
 * @property mixed $data_fim
 * @property mixed $data_inicio
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AfastamentoFeedback newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AfastamentoFeedback newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AfastamentoFeedback query()
 * @mixin \Eloquent
 * @property int $id
 * @property int $feedback_id
 * @property int $quem_cadastrou
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AfastamentoFeedback whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AfastamentoFeedback whereDataFim($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AfastamentoFeedback whereDataInicio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AfastamentoFeedback whereFeedbackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AfastamentoFeedback whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AfastamentoFeedback whereQuemCadastrou($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AfastamentoFeedback whereUpdatedAt($value)
 */
class AfastamentoFeedback extends Model
{
    use LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'AfastamentoFeedback';
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
        'id',
        'feedback_id',
        'quem_cadastrou',
        'data_inicio',
        'data_fim',
    ];

    protected $casts = [
        'feedback_id' => 'int',
        'quem_cadastrou' => 'int',
        'data_inicio' => 'date:d/m/Y',
        'data_fim' => 'date:d/m/Y',

    ];

    protected $table = 'afastamento_feedbacks';


    //Acessor ->dataInicio
    public function getDataInicioAttribute($value)
    {
        $data = new DataHora($this->attributes['data_inicio']);
        return $data->dataCompleta();
    }

    //Modificador ->dataInicio
    public function setDataInicioAttribute($value)
    {
        $data = new DataHora($value);
        $this->attributes['data_inicio'] = $data->dataInsert();
    }

    //Acessor ->DataFim
    public function getDataFimAttribute($value)
    {
        $data = new DataHora($this->attributes['data_fim']);
        return $data->dataCompleta();
    }

    //Modificador ->DataFim
    public function setDataFimAttribute($value)
    {
        $data = new DataHora($value);
        $this->attributes['data_fim'] = $data->dataInsert();
    }

    public function Feedback()
    {
        return $this->hasOne(FeedbackCurriculo::class, 'id', 'feedback_id');
    }

    public function Usuario()
    {
        return $this->hasOne(User::class, 'id', 'quem_cadastrou');
    }

}
