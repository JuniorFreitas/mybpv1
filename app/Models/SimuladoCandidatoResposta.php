<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\SimuladoCandidatoResposta
 *
 * @property int $simulado_vaga_id
 * @property int $curriculo_id_id
 * @property int $simulado_pergunta_id
 * @property int $simulado_resposta_id
 * @property-read \App\Models\Curriculo $Candidato
 * @property-read \App\Models\SimuladoPergunta $Perguntas
 * @property-read \App\Models\SimuladoResposta $Resposta
 * @property-read \App\Models\SimuladoCandidato $SimuladoCandidato
 * @property-read \App\Models\SimuladoVaga $SimuladoVaga
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SimuladoCandidatoResposta newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SimuladoCandidatoResposta newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SimuladoCandidatoResposta query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SimuladoCandidatoResposta whereCurriculoIdId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SimuladoCandidatoResposta whereSimuladoPerguntaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SimuladoCandidatoResposta whereSimuladoRespostaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SimuladoCandidatoResposta whereSimuladoVagaId($value)
 * @mixin \Eloquent
 * @property int $curriculo_id
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SimuladoCandidatoResposta whereCurriculoId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Activitylog\Models\Activity[] $activities
 * @property-read int|null $activities_count
 * @property int|null $feedback_id
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoCandidatoResposta whereFeedbackId($value)
 */
class SimuladoCandidatoResposta extends Model
{
    use LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'SimuladoCandidatoResposta';
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

    protected $primaryKey = 'simulado_vaga_id';
    protected $fillable = [
        'simulado_vaga_id',
        'curriculo_id',
        'simulado_resposta_id',
        'simulado_pergunta_id',
    ];

    protected $casts = [
        'simulado_vaga_id' => 'int',
        'curriculo_id' => 'int',
        'simulado_resposta_id' => 'int',
        'simulado_pergunta_id' => 'int',
    ];

    public $timestamps = false;

    public function SimuladoVaga()
    {
        return $this->hasOne(SimuladoVaga::class, 'id', 'simulado_vaga_id');
    }

    public function SimuladoCandidato()
    {
        return $this->hasOne(SimuladoCandidato::class, 'curriculo_id', 'curriculo_id');
    }

    public function Candidato()
    {
        return $this->hasOne(Curriculo::class, 'id', 'curriculo_id');
    }

    public function Perguntas()
    {
        return $this->hasOne(SimuladoPergunta::class, 'id', 'simulado_pergunta_id');
    }

    public function Resposta()
    {
        return $this->hasOne(SimuladoResposta::class, 'id', 'simulado_resposta_id');
    }

}
