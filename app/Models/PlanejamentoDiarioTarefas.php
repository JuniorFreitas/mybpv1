<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PlanejamentoDiarioTarefas
 *
 * @property int $id
 * @property int $planejamento_id
 * @property string $tarefa
 * @property string $status
 * @property-read \App\Models\PlanejamentoDiario|null $PlanejamentoDiario
 * @method static \Illuminate\Database\Eloquent\Builder|PlanejamentoDiarioTarefas newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PlanejamentoDiarioTarefas newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PlanejamentoDiarioTarefas query()
 * @method static \Illuminate\Database\Eloquent\Builder|PlanejamentoDiarioTarefas whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlanejamentoDiarioTarefas wherePlanejamentoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlanejamentoDiarioTarefas whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlanejamentoDiarioTarefas whereTarefa($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @mixin \Eloquent
 */
class PlanejamentoDiarioTarefas extends Model
{

    use LogsActivity, HasActivitylogOptions;

    protected static $logName = 'PlanejamentoDiarioTarefas';
    protected $fillable = [
        'planejamento_id',
        'tarefa',
        'status',
    ];

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

    protected $casts = [
        'planejamento_id' => 'int',
        'tarefa' => 'string',
        'status' => 'string',
    ];

    public $timestamps = false;

    public function PlanejamentoDiario()
    {
        return $this->hasOne(PlanejamentoDiario::class, 'id', 'planejamento_id');
    }
}
