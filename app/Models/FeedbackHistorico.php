<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;

/**
 * App\Models\FeedbackHistorico
 *
 * @property int $id
 * @property int $feedback_id
 * @property string $situacao
 * @property string $descricao
 * @property string $compromisso
 * @property string $data
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\FeedbackCurriculo $Feedback
 * @property-read \App\Models\User $UsuarioRelator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static Builder|FeedbackHistorico newModelQuery()
 * @method static Builder|FeedbackHistorico newQuery()
 * @method static Builder|FeedbackHistorico query()
 * @method static Builder|FeedbackHistorico whereCompromisso($value)
 * @method static Builder|FeedbackHistorico whereCreatedAt($value)
 * @method static Builder|FeedbackHistorico whereData($value)
 * @method static Builder|FeedbackHistorico whereDescricao($value)
 * @method static Builder|FeedbackHistorico whereFeedbackId($value)
 * @method static Builder|FeedbackHistorico whereId($value)
 * @method static Builder|FeedbackHistorico whereSituacao($value)
 * @method static Builder|FeedbackHistorico whereUpdatedAt($value)
 * @method static Builder|FeedbackHistorico whereUserId($value)
 * @mixin \Eloquent
 */
class FeedbackHistorico extends Model
{
    use HasFactory, LogsActivity, HasActivitylogOptions;

    protected static $logFillable = true;
    protected static $logName = 'feedback_historico';
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
        'situacao',
        'descricao',
        'compromisso',
        'data',
        'user_id',
    ];

    protected $casts = [
        'id' => 'int',
        'feedback_id' => 'int',
        'situacao' => 'string',
        'descricao' => 'string',
        'compromisso' => 'string',
        'data' => 'string',
        'user_id' => 'int',
    ];

    public function setDataAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($value);
            $this->attributes['data'] = $data->dataInsert();
        } else {
            $this->attributes['data'] = null;
        }
    }

    //Acessor ->entrevista
    public function getDataAttribute($value)
    {
        $data = new DataHora($this->attributes['data']);
        return $data->dataCompleta();
    }

    public function Feedback()
    {
        return $this->belongsTo(FeedbackCurriculo::class, 'feedback_id');
    }

    public function UsuarioRelator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->user_id = auth()->id();
        });
    }
}
