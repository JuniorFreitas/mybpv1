<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\NpsResposta
 *
 * @property int $id
 * @property int $user_id
 * @property int $empresa_id
 * @property int|null $nps_ciclo_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\NpsRespostaItem> $itens
 * @property-read \App\Models\NpsCiclo|null $npsCiclo
 * @property-read \App\Models\User $User
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read int|null $itens_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NpsResposta newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NpsResposta newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NpsResposta query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NpsResposta whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NpsResposta whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NpsResposta whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NpsResposta whereNpsCicloId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NpsResposta whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NpsResposta whereUserId($value)
 * @mixin \Eloquent
 */
class NpsResposta extends Model
{

    use LogsActivity, HasActivitylogOptions;

    protected static $logName = 'NpsResposta';
    protected $table = 'nps_respostas';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

    protected $fillable = [
        'user_id',
        'empresa_id',
        'nps_ciclo_id',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'empresa_id' => 'integer',
        'nps_ciclo_id' => 'integer',
    ];

    public function User()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function npsCiclo()
    {
        return $this->belongsTo(NpsCiclo::class, 'nps_ciclo_id');
    }

    public function itens()
    {
        return $this->hasMany(NpsRespostaItem::class, 'nps_resposta_id');
    }
}
