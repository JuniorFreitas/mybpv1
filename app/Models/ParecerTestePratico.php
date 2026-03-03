<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;

/**
 * App\Models\ParecerTestePratico
 *
 * @property int $id
 * @property int|null $feedback_id
 * @property bool|null $fez_teste
 * @property string|null $data_horario_realizacao
 * @property string|null $responsavel_pelo_teste
 * @property string|null $qual_teste
 * @property int|null $resultado_teste
 * @property int|null $nota_teste
 * @property string|null $parecer_final_teste
 * @property int|null $entrevistador
 * @property string|null $quem_entrevistou
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $formulario_id
 * @property-read \App\Models\Curriculo|null $Curriculo
 * @property-read \App\Models\User|null $Entrevistador
 * @property-read \App\Models\FeedbackCurriculo|null $FeedbackCurriculo
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read mixed $nota_teste_format
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerTestePratico newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerTestePratico newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerTestePratico query()
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerTestePratico whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerTestePratico whereDataHorarioRealizacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerTestePratico whereEntrevistador($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerTestePratico whereFeedbackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerTestePratico whereFezTeste($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerTestePratico whereFormularioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerTestePratico whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerTestePratico whereNotaTeste($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerTestePratico whereParecerFinalTeste($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerTestePratico whereQualTeste($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerTestePratico whereQuemEntrevistou($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerTestePratico whereResponsavelPeloTeste($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerTestePratico whereResultadoTeste($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ParecerTestePratico whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ParecerTestePratico extends Model
{
    use HasFactory, LogsActivity, HasActivitylogOptions;

    protected static $logFillable = true;
    protected static $logName = 'parecer_teste_pratico';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    public function getDescriptionForEvent(string $eventName): string {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = "";
    }

    protected $table = 'parecer_teste_pratico';

    protected $fillable = [
        'feedback_id',
        'formulario_id',
        'fez_teste',
        'data_horario_realizacao',
        'responsavel_pelo_teste',
        'qual_teste',
        'resultado_teste',
        'nota_teste',
        'parecer_final_teste',
        'entrevistador',
        'quem_entrevistou'
    ];

    protected $casts = [
        'id' => 'int',
        'feedback_id' => 'int',
        'formulario_id' => 'int',
        'fez_teste' => 'boolean',
        'data_horario_realizacao' => 'string',
        'responsavel_pelo_teste' => 'string',
        'qual_teste' => 'string',
        'resultado_teste' => 'int',
        'nota_teste' => 'int',
        'parecer_final_teste' => 'string',
        'entrevistador' => 'int',
        'quem_entrevistou' => 'string',
    ];

    protected $appends = ['NotaTesteFormat'];

    //Modificador ->horario_realizacao
    public function setDataHorarioRealizacaoAttribute($value)
    {
        if (!is_null($value)) {
            $newTime = explode(' às ', $value);
            $newDH = $newTime[0] . ' ' . $newTime[1].':00';
            $data = new DataHora($newDH);
            $this->attributes['data_horario_realizacao'] = $data->dataHoraInsert();
        }
        else{
            $this->attributes['data_horario_realizacao'] = null;
        }
    }

    //Acessor ->horario_realizacao
    public function getDataHorarioRealizacaoAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($this->attributes['data_horario_realizacao']);
            return $data->dataCompleta() . ' às ' . $data->hora() . ':' . $data->minuto();
        }else {
            return null;
        }
    }

    public function getNotaTesteFormatAttribute($value)
    {
        return $this->attributes['nota_teste'] == 0 ? 'Não se aplica' : $this->attributes['nota_teste'];
//        return $this->nota_teste == 0 ? 'Não se aplica' : $this->nota_teste;
    }

    public function FeedbackCurriculo()
    {
        return $this->hasOne(FeedbackCurriculo::class, 'id', 'feedback_id');
    }

    public function Curriculo()
    {
        return $this->hasOne(Curriculo::class, 'id', 'curriculo_id');
    }

    public function Entrevistador()
    {
        return $this->hasOne(User::class, 'id', 'entrevistador');
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->entrevistador = auth()->id();
        });

        static::updating(function ($model) {
            $model->entrevistador = auth()->id();
        });
    }
}
