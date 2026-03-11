<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ManutencaoProgramada
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ManutencaoProgramada newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ManutencaoProgramada newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ManutencaoProgramada query()
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @mixin \Eloquent
 */
class ManutencaoProgramada extends Model
{
    use LogsActivity, HasActivitylogOptions, HasFactory;

    protected static $logName = 'ManutencaoProgramada';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

}
