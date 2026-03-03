<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;

/**
 * App\Models\LogHistorico
 *
 * @property int $id
 * @property int $feedback_id
 * @property int $empresa_id
 * @property string $acao
 * @property int $user_id
 * @property string $data
 * @property-read \App\Models\FeedbackCurriculo|null $Feedback
 * @property-read \App\Models\User|null $Usuario
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|LogHistorico newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LogHistorico newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LogHistorico query()
 * @method static \Illuminate\Database\Eloquent\Builder|LogHistorico whereAcao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogHistorico whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogHistorico whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogHistorico whereFeedbackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogHistorico whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogHistorico whereUserId($value)
 * @mixin \Eloquent
 */
class LogHistorico extends Model
{
    use HasFactory, LogsActivity, HasActivitylogOptions, TenantTrait;

    protected static $logFillable = true;
    protected static $logName = 'log_historico';
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
        'empresa_id',
        'acao',
        'user_id',
        'data',
    ];
    protected $casts = [
        'feedback_id' => 'int',
        'empresa_id' => 'int',
        'acao' => 'string',
        'user_id' => 'int',
        'data' => 'string',
    ];

    public $timestamps = false;

    protected $table = 'log_historico';

    public function getDataAttribute($value)
    {
        if ($value) {
            $data = new DataHora($this->attributes['data']);
            return $data->dataHoraCompleta();
        }
    }

    public function setDataAttribute($value)
    {
        if ($value) {
            $data = new DataHora($value);
            $this->attributes['data'] = $data->dataHoraInsert();
        }
    }

    public function Feedback()
    {
        return $this->hasOne(FeedbackCurriculo::class, 'id', 'feedback_id');
    }

    public function Usuario()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public static function createLog($feedbackId, $acao)
    {
        return static::create([
            'feedback_id' => $feedbackId,
            'acao' => $acao,
            'data' => (new DataHora())->dataHoraInsert()
        ]);
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->user_id = auth()->id();
        });
    }
}
