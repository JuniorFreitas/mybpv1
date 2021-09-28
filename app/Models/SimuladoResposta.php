<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\SimuladoResposta
 *
 * @property int $id
 * @property int $simulado_pergunta_id
 * @property string $resposta
 * @property bool $correto
 * @property-read \App\Models\SimuladoPergunta $Pergunta
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SimuladoResposta newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SimuladoResposta newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SimuladoResposta query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SimuladoResposta whereCorreto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SimuladoResposta whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SimuladoResposta whereResposta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SimuladoResposta whereSimuladoPerguntaId($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Activitylog\Models\Activity[] $activities
 * @property-read int|null $activities_count
 */
class SimuladoResposta extends Model
{
    use LogsActivity;

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
