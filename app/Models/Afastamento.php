<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\Afastamento
 *
 * @property int $id
 * @property int $feedback_id
 * @property int $cadastrado_id
 * @property string $motivo
 * @property string $data_inicio
 * @property string $data_fim
 * @property string|null $observacao
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $Anexos
 * @property-read int|null $anexos_count
 * @property-read \App\Models\FeedbackCurriculo|null $Feedback
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @property-read mixed $periodo
 * @method static \Illuminate\Database\Eloquent\Builder|Afastamento newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Afastamento newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Afastamento query()
 * @method static \Illuminate\Database\Eloquent\Builder|Afastamento whereCadastradoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Afastamento whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Afastamento whereDataFim($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Afastamento whereDataInicio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Afastamento whereFeedbackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Afastamento whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Afastamento whereMotivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Afastamento whereObservacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Afastamento whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Afastamento extends Model
{
    use HasFactory, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'afastamento';
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
        'cadastrado_id',
        'motivo',
        'data_inicio',
        'data_fim',
        'observacao',
    ];

    protected $casts = [
        'feedback_id' => 'int',
        'cadastrado_id' =>'int',
        'motivo' => 'string',
        'data_inicio' => 'string',
        'data_fim' => 'string',
        'observacao' =>'string',
    ];

    protected $appends = ['periodo'];

    public function getPeriodoAttribute()
    {
        $dataInicio = (new DataHora($this->data_inicio))->dataCompleta();
        $dataFim = (new DataHora($this->data_fim))->dataCompleta();

        return "{$dataInicio} até {$dataFim}";
    }

    public function Anexos()
    {
        return $this->belongsToMany(Arquivo::class, 'afastamento_anexos', 'afastamento_id', 'arquivo_id');
    }

    public function Feedback()
    {
        return $this->hasOne(FeedbackCurriculo::class, 'id', 'feedback_id');
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            $periodo = explode(' até ', $model['periodo']);
            $model->cadastrado_id = auth()->check() ? auth()->id() : $model->cadastrado_id;
            $model->data_inicio = (new DataHora($periodo[0]))->dataInsert();
            $model->data_fim = (new DataHora($periodo[1]))->dataInsert();
        });

        static::updating(function ($model) {
            $periodo = explode(' até ', $model['periodo']);
            $model->data_inicio = (new DataHora($periodo[0]))->dataInsert();
            $model->data_fim = (new DataHora($periodo[1]))->dataInsert();
        });

    }

}
