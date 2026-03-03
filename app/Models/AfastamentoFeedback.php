<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;

/**
 * App\Models\AfastamentoFeedback
 *
 * @property int $id
 * @property int $feedback_id
 * @property int $quem_cadastrou
 * @property \Illuminate\Support\Carbon|null $data_inicio
 * @property \Illuminate\Support\Carbon|null $data_fim
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\FeedbackCurriculo|null $Feedback
 * @property-read User|null $Usuario
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|AfastamentoFeedback newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AfastamentoFeedback newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AfastamentoFeedback query()
 * @method static \Illuminate\Database\Eloquent\Builder|AfastamentoFeedback whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AfastamentoFeedback whereDataFim($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AfastamentoFeedback whereDataInicio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AfastamentoFeedback whereFeedbackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AfastamentoFeedback whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AfastamentoFeedback whereQuemCadastrou($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AfastamentoFeedback whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class AfastamentoFeedback extends Model
{
    use LogsActivity, HasActivitylogOptions;

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
