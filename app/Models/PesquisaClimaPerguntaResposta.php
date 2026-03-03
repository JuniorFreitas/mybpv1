<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PesquisaClimaPerguntaResposta
 *
 * @property int $id
 * @property int $pergunta_id
 * @property string $resposta
 * @property int $ativo
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PesquisaClimaPerguntaRespostaCandidato> $PerguntaResposta
 * @property-read int|null $pergunta_resposta_count
 * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaPerguntaResposta newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaPerguntaResposta newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaPerguntaResposta query()
 * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaPerguntaResposta whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaPerguntaResposta whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaPerguntaResposta wherePerguntaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaPerguntaResposta whereResposta($value)
 * @mixin \Eloquent
 */
class PesquisaClimaPerguntaResposta extends Model
{

    use LogsActivity, HasActivitylogOptions;

    protected static $logName = 'PesquisaClimaPerguntaResposta';
    protected $fillable = [
        'pergunta_id',
        'resposta',
        'ativo'
    ];

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

    public $timestamps = false;

    public function PerguntaResposta()
    {
        return $this->hasMany(PesquisaClimaPerguntaRespostaCandidato::class, 'resposta_id', 'id');
    }
}
