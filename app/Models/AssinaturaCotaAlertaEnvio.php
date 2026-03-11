<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $empresa_id
 * @property string $competencia
 * @property int $percentual
 * @property int $usadas
 * @property int $limite
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssinaturaCotaAlertaEnvio newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssinaturaCotaAlertaEnvio newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssinaturaCotaAlertaEnvio query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssinaturaCotaAlertaEnvio whereCompetencia($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssinaturaCotaAlertaEnvio whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssinaturaCotaAlertaEnvio whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssinaturaCotaAlertaEnvio whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssinaturaCotaAlertaEnvio whereLimite($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssinaturaCotaAlertaEnvio wherePercentual($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssinaturaCotaAlertaEnvio whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssinaturaCotaAlertaEnvio whereUsadas($value)
 * @mixin \Eloquent
 */
class AssinaturaCotaAlertaEnvio extends Model
{
    use LogsActivity, HasActivitylogOptions, HasFactory;

    protected static $logName = 'AssinaturaCotaAlertaEnvio';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    protected $table = 'assinatura_cota_alerta_envios';

    protected $fillable = [
        'empresa_id',
        'competencia',
        'percentual',
        'usadas',
        'limite',
    ];

    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'percentual' => 'int',
        'usadas' => 'int',
        'limite' => 'int',
    ];

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }
}
