<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Acessos
 *
 * @property int $id
 * @property int $user_id
 * @property string $ip
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Acessos newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Acessos newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Acessos query()
 * @method static \Illuminate\Database\Eloquent\Builder|Acessos whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Acessos whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Acessos whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Acessos whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Acessos whereUserId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @mixin \Eloquent
 */
class Acessos extends Model
{
    use LogsActivity, HasActivitylogOptions, HasFactory;

    protected static $logName = 'Acessos';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

}
