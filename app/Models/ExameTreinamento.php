<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\ExameTreinamento
 *
 * @property int $id
 * @property int $feedback_id
 * @property bool $exame_realizado
 * @property \Illuminate\Support\Carbon|null $data_realizado
 * @property string|null $tipo_exame
 * @property bool|null $trabalho_altura
 * @property bool|null $espaco_confinado
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\FeedbackCurriculo|null $Feedback
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|ExameTreinamento newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExameTreinamento newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExameTreinamento query()
 * @method static \Illuminate\Database\Eloquent\Builder|ExameTreinamento whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameTreinamento whereDataRealizado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameTreinamento whereEspacoConfinado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameTreinamento whereExameRealizado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameTreinamento whereFeedbackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameTreinamento whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameTreinamento whereTipoExame($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameTreinamento whereTrabalhoAltura($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameTreinamento whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameTreinamento whereUserId($value)
 * @mixin \Eloquent
 */
class ExameTreinamento extends Model
{
    use HasFactory, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'ExameTreinamento';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    public function getDescriptionForEvent(string $eventName): string {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = "";
    }

    protected $fillable = [
        'feedback_id',
        'exame_realizado',
        'data_realizado',
        'tipo_exame',
        'trabalho_altura',
        'espaco_confinado',
        'user_id',
    ];

    protected $casts = [
        'id' => 'int',
        'feedback_id' => 'int',
        'exame_realizado' => 'boolean',
        'data_realizado' => 'date',
        'tipo_exame' => 'string',
        'trabalho_altura' => 'boolean',
        'espaco_confinado' => 'boolean',
        'user_id' => 'int',
    ];


    //Acessor ->data_nr_trinta_tres
    public function getDataRealizadoAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($this->attributes['data_realizado']);
            return $data->dataCompleta();
        }
    }

    //SET ->data_nr_trinta_tres
    public function setDataRealizadoAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($value);
            $this->attributes['data_realizado'] = $data->dataInsert();
        }else{
            $this->attributes['data_realizado'] = null;
        }
    }

    public function Feedback()
    {
        return $this->hasOne(FeedbackCurriculo::class, 'id', 'feedback_id');
    }
}
