<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\AvaliacaoNoventaDias
 *
 * @property int $id
 * @property string $pergunta
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaDias newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaDias newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaDias query()
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaDias whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaDias whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaDias wherePergunta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoNoventaDias whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class AvaliacaoNoventaDias extends Model
{
    use LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'area';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    public function getDescriptionForEvent(string $eventName)
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = "";
    }

    protected $table = 'formulario_avaliacao_noventa';
    protected $fillable = ['pergunta'];
    protected $casts = ['pergunta' => 'string'];
}
