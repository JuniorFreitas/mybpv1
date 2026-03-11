<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\NpsRespostaItem
 *
 * @property int $id
 * @property int $nps_resposta_id
 * @property int $nps_pergunta_id
 * @property int $nota
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\NpsPergunta $npsPergunta
 * @property-read \App\Models\NpsResposta $npsResposta
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NpsRespostaItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NpsRespostaItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NpsRespostaItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NpsRespostaItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NpsRespostaItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NpsRespostaItem whereNota($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NpsRespostaItem whereNpsPerguntaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NpsRespostaItem whereNpsRespostaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NpsRespostaItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class NpsRespostaItem extends Model
{

    use LogsActivity, HasActivitylogOptions;

    protected static $logName = 'NpsRespostaItem';
    protected $table = 'nps_resposta_itens';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

    protected $fillable = [
        'nps_resposta_id',
        'nps_pergunta_id',
        'nota',
    ];

    protected $casts = [
        'nps_resposta_id' => 'integer',
        'nps_pergunta_id' => 'integer',
        'nota' => 'integer',
    ];

    public function npsResposta()
    {
        return $this->belongsTo(NpsResposta::class, 'nps_resposta_id');
    }

    public function npsPergunta()
    {
        return $this->belongsTo(NpsPergunta::class, 'nps_pergunta_id');
    }
}
