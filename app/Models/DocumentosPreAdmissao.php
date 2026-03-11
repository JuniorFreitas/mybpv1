<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\DocumentosPreAdmissao
 *
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentosPreAdmissao newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentosPreAdmissao newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentosPreAdmissao query()
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @mixin \Eloquent
 */
class DocumentosPreAdmissao extends Model
{

    use LogsActivity, HasActivitylogOptions;

    protected static $logName = 'DocumentosPreAdmissao';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

    //
}
