<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\AtaReuniaoParticipante
 *
 * @property int $id
 * @property int $ata_reuniao_id
 * @property string|null $nome
 * @property int|null $user_id
 * @property string $funcao
 * @property-read \App\Models\AtaReuniao|null $AtaReuniao
 * @property-read User|null $User
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoParticipante newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoParticipante newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoParticipante query()
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoParticipante whereAtaReuniaoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoParticipante whereFuncao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoParticipante whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoParticipante whereNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AtaReuniaoParticipante whereUserId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @mixin \Eloquent
 */
class AtaReuniaoParticipante extends Model
{

    use LogsActivity, HasActivitylogOptions;

    protected static $logName = 'AtaReuniaoParticipante';
    protected $fillable = [
        'ata_reuniao_id',
        'nome',
        'user_id',
        'funcao',
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

    public function User()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
