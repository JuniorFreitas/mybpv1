<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\AtaReuniaoAssunto
 *
 * @property int $id
 * @property int $ata_reuniao_id
 * @property string $assunto
 * @property-read \App\Models\AtaReuniao|null $AtaReuniao
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAssunto newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAssunto newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAssunto query()
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAssunto whereAssunto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAssunto whereAtaReuniaoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoAssunto whereId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @mixin \Eloquent
 */
class AtaReuniaoAssunto extends Model
{

    use LogsActivity, HasActivitylogOptions;

    protected static $logName = 'AtaReuniaoAssunto';
    protected $fillable = [
        'ata_reuniao_id',
        'assunto',
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

    public function AtaReuniao()
    {
        return $this->hasOne(AtaReuniao::class, 'id', 'ata_reuniao_id');
    }
}
