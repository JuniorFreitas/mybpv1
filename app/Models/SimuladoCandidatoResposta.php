<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;

/**
 * App\Models\SimuladoCandidatoResposta
 *
 * @property int $simulado_vaga_id
 * @property int|null $feedback_id
 * @property int $simulado_pergunta_id
 * @property int $simulado_resposta_id
 * @property-read \App\Models\Curriculo|null $Candidato
 * @property-read \App\Models\FeedbackCurriculo|null $Feedback
 * @property-read \App\Models\SimuladoPergunta|null $Perguntas
 * @property-read \App\Models\SimuladoResposta|null $Resposta
 * @property-read \App\Models\SimuladoCandidato|null $SimuladoCandidato
 * @property-read \App\Models\SimuladoVaga|null $SimuladoVaga
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoCandidatoResposta newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoCandidatoResposta newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoCandidatoResposta query()
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoCandidatoResposta whereFeedbackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoCandidatoResposta whereSimuladoPerguntaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoCandidatoResposta whereSimuladoRespostaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoCandidatoResposta whereSimuladoVagaId($value)
 * @mixin \Eloquent
 */
class SimuladoCandidatoResposta extends Model
{
    use LogsActivity, HasActivitylogOptions;

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
        'feedback_id',
        'simulado_resposta_id',
        'simulado_pergunta_id',
    ];

    protected $casts = [
        'simulado_vaga_id' => 'int',
        'feedback_id' => 'int',
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

    public function Feedback()
    {
        return $this->hasOne(FeedbackCurriculo::class, 'id', 'feedback_id');
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
