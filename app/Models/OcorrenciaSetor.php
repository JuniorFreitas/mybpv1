<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\OcorrenciaSetor
 *
 * @property int $id
 * @property string $nome
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OcorrenciaSetor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OcorrenciaSetor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OcorrenciaSetor query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OcorrenciaSetor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\OcorrenciaSetor whereNome($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Activitylog\Models\Activity[] $activities
 * @property-read int|null $activities_count
 */
class OcorrenciaSetor extends Model {
    use LogsActivity;
    protected static $logFillable = true;
    protected static $logName = 'ocorrencias';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    public function getDescriptionForEvent(string $eventName): string {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName) {
        $activity->descricao = "";
    }

    protected $table = 'ocorrencias_setores';
    protected $fillable = ['nome'];
    protected $casts = [
        'id' => 'int',
        'nome' => 'string',
    ];
    public $timestamps = false;


}
