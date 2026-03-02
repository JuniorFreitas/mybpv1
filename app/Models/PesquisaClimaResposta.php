<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PesquisaClimaResposta
 *
 * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaResposta newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaResposta newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PesquisaClimaResposta query()
 * @mixin \Eloquent
 */
class PesquisaClimaResposta extends Model
{

    use LogsActivity, HasActivitylogOptions;

    protected static $logName = 'PesquisaClimaResposta';
    protected $fillable = [
        'tipo_id',
        'feedback_id',
        'pergunta_id',
        'resposta',
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
        'tipo_id' => 'int',
        'feedback_id' => 'int',
        'pergunta_id' => 'int',
        'resposta' => 'string',
    ];

}
