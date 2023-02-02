<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\PeriodoJornada
 *
 * @property-read \App\Models\EscalaJornada|null $Jornada
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Activitylog\Models\Activity[] $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodoJornada newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodoJornada newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodoJornada query()
 * @mixin \Eloquent
 * @property int $id
 * @property int $jornada_id
 * @property mixed $entrada
 * @property mixed $saida
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodoJornada whereEntrada($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodoJornada whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodoJornada whereJornadaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodoJornada whereSaida($value)
 */
class PeriodoJornada extends Model
{
    use HasFactory,LogsActivity, SoftDeletes;
    protected static $logFillable = true;
    protected static $logName = 'PeriodoJornada';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;
    public $timestamps=false;
    protected $table = 'periodo_jornadas';
    protected $fillable = [
        'jornada_id' ,
        'entrada' ,
        'saida' ,
    ];
    protected $casts = [
        'id' => 'int',
        'jornada_id' => 'int',
        'entrada' => 'datetime:H:i',
        'saida' => 'datetime:H:i',

    ];

    public function getDescriptionForEvent(string $eventName): string {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName) {
        $activity->descricao = "";
    }

    protected function serializeDate(DateTimeInterface $date) {
        return $date->format('Y-m-d H:i:s');
    }

    public function Jornada(){
        return $this->hasOne(EscalaJornada::class,'id','jornada_id');
    }
}
