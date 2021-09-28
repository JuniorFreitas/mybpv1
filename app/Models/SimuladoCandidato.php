<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\SimuladoCandidato
 *
 * @property int $id
 * @property int $simulado_id
 * @property int $curriculo_id
 * @property int $duracao_segundos
 * @property bool $finalizado
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Curriculo $Candidato
 * @property-read \App\Models\Simulado $Simulado
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SimuladoCandidato newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SimuladoCandidato newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SimuladoCandidato query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SimuladoCandidato whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SimuladoCandidato whereCurriculoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SimuladoCandidato whereDuracaoSegundos($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SimuladoCandidato whereFinalizado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SimuladoCandidato whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SimuladoCandidato whereSimuladoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SimuladoCandidato whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int $simulado_vaga_id
 * @property mixed|null $data_finalizacao
 * @property-read \App\Models\SimuladoVaga $SimuladoVaga
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SimuladoCandidato whereDataFinalizacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SimuladoCandidato whereSimuladoVagaId($value)
 * @property int|null $acertos
 * @property string|null $status
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Activitylog\Models\Activity[] $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SimuladoCandidato whereAcertos($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SimuladoCandidato whereStatus($value)
 * @property int|null $feedback_id
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoCandidato whereFeedbackId($value)
 * @property-read mixed $tempo_execucao
 */
class SimuladoCandidato extends Model
{
    use LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'SimuladoCandidato';
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
        'simulado_vaga_id',
        'curriculo_id',
        'duracao_segundos',
        'finalizado',
        'data_finalizacao',
        'acertos',
        'status',
    ];
    protected $casts = [
        'id' => 'int',
        'simulado_vaga_id' => 'int',
        'curriculo_id' => 'int',
        'duracao_segundos' => 'int',
        'finalizado' => 'boolean',
        'data_finalizacao' => 'date:d/m/Y',
        'acertos' => 'int',
        'status' => 'string',
    ];

    //Acessor ->data_finalizacao
    public function getDataFinalizacaoAttribute($value)
    {
        if ($value) {
            $data = new DataHora($this->attributes['data_finalizacao']);
            return $data->dataCompleta() . ' ' . $data->hora() . ':' . $data->minuto();
        }
    }

    //Acessor ->data_finalizacao
    public function setDataFinalizacaoAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($value);
            $this->attributes['data_finalizacao'] = $data->dataHoraInsert();
        }
        return null;
    }


    public function SimuladoVaga()
    {
        return $this->hasOne(SimuladoVaga::class, 'id', 'simulado_vaga_id');
    }

    public function Candidato()
    {
        return $this->hasOne(Curriculo::class, 'id', 'curriculo_id');
    }

//    public function SimuladoAlunoResposta()
//    {
//        return
//    }

}
