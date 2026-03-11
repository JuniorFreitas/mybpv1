<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PeriodoAquisitivo
 *
 * @property int $id
 * @property string $label
 * @property int $ano_inicial
 * @property int $ano_final
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodoAquisitivo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodoAquisitivo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodoAquisitivo query()
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodoAquisitivo whereAnoFinal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodoAquisitivo whereAnoInicial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodoAquisitivo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodoAquisitivo whereLabel($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @mixin \Eloquent
 */
class PeriodoAquisitivo extends Model
{
    use LogsActivity, HasActivitylogOptions, HasFactory;

    protected static $logName = 'PeriodoAquisitivo';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

    protected $table = 'periodos_aquisitivos';

    protected $fillable = [
        'label',
        'ano_inicial',
        'ano_final',
    ];
    protected $casts = [
        'label' => 'string',
        'ano_inicial' => 'int',
        'ano_final' => 'int',
    ];

}
