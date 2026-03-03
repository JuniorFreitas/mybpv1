<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

