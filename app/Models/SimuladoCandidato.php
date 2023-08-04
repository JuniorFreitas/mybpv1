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
 * @property int $simulado_vaga_id
 * @property int|null $feedback_id
 * @property int $duracao_segundos
 * @property bool $finalizado
 * @property mixed|null $data_finalizacao
 * @property int|null $acertos
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $status
 * @property int|null $empresa_id
 * @property-read \App\Models\FeedbackCurriculo|null $Candidato
 * @property-read \App\Models\SimuladoVaga|null $SimuladoVaga
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoCandidato newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoCandidato newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoCandidato query()
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoCandidato whereAcertos($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoCandidato whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoCandidato whereDataFinalizacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoCandidato whereDuracaoSegundos($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoCandidato whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoCandidato whereFeedbackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoCandidato whereFinalizado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoCandidato whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoCandidato whereSimuladoVagaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoCandidato whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoCandidato whereUpdatedAt($value)
 * @mixin \Eloquent
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
        'feedback_id',
        'duracao_segundos',
        'finalizado',
        'data_finalizacao',
        'acertos',
        'status',
        'empresa_id',
    ];
    protected $casts = [
        'id' => 'int',
        'simulado_vaga_id' => 'int',
        'feedback_id' => 'int',
        'duracao_segundos' => 'int',
        'finalizado' => 'boolean',
        'data_finalizacao' => 'date:d/m/Y',
        'acertos' => 'int',
        'status' => 'string',
        'empresa_id' => 'int',
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
        return $this->hasOne(FeedbackCurriculo::class, 'id', 'feedback_id');
    }

//    public function SimuladoAlunoResposta()
//    {
//        return
//    }

}
