<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\FormularioAvaliacaoAnual
 *
 * @property int $id
 * @property string $pergunta
 * @property int $topicos_id
 * @property-read \App\Models\Topicos|null $Topicos
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|FormularioAvaliacaoAnual newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FormularioAvaliacaoAnual newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FormularioAvaliacaoAnual query()
 * @method static \Illuminate\Database\Eloquent\Builder|FormularioAvaliacaoAnual whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormularioAvaliacaoAnual wherePergunta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormularioAvaliacaoAnual whereTopicosId($value)
 * @mixin \Eloquent
 */
class FormularioAvaliacaoAnual extends Model
{
    use LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'FormularioAvaliacaoAnual';
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

    protected $fillable = ['pergunta', 'topicos_id'];

    protected $casts = [
        'pergunta' => 'string',
        'topicos_id' => 'int'
    ];

    protected $table = 'formulario_avaliacao_anuals';

    public $timestamps = false;

    public function Topicos()
    {
        return $this->hasOne(Topicos::class, 'id', 'topicos_id');
    }
}
