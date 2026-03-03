<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;

/**
 * App\Models\SimuladoResposta
 *
 * @property int $id
 * @property int $simulado_pergunta_id
 * @property string $resposta
 * @property bool $correto
 * @property-read \App\Models\SimuladoPergunta|null $Pergunta
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoResposta newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoResposta newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoResposta query()
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoResposta whereCorreto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoResposta whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoResposta whereResposta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SimuladoResposta whereSimuladoPerguntaId($value)
 * @mixin \Eloquent
 */
class SimuladoResposta extends Model
{
    use LogsActivity, HasActivitylogOptions;

    protected static $logFillable = true;
    protected static $logName = 'SimuladoResposta';
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

//    protected $hidden = ['correto'];
    protected $fillable = [
        'simulado_pergunta_id',
        'resposta',
        'correto',
    ];

    protected $casts = [
        'id' => 'int',
        'simulado_pergunta_id' => 'int',
        'resposta' => 'string',
        'correto' => 'boolean',
    ];

    public $timestamps = false;

    public function Pergunta()
    {
        return $this->hasOne(SimuladoPergunta::class, 'id', 'simulado_pergunta_id');
    }
}
