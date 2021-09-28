<?php

namespace App\Models;

use App\Scopes\ScopeEmpresa;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\Feriado
 *
 * @property int $id
 * @property string $descricao
 * @property string $data
 * @property string $ativo
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Feriado whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Feriado whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Feriado whereDescricao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Feriado whereId($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Feriado newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Feriado newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Feriado query()
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Activitylog\Models\Activity[] $activities
 * @property-read int|null $activities_count
 * @property int $empresa_id
 * @method static \Illuminate\Database\Eloquent\Builder|Feriado whereEmpresaId($value)
 */
class Feriado extends Model {
    use LogsActivity;
    protected static $logFillable = true;
    protected static $logName = 'controle_ponto.feriado';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    public function getDescriptionForEvent(string $eventName): string {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName) {
        $activity->descricao = "";
    }

    protected $fillable = [
        'descricao',
        'data',
        'ativo'
    ];

    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'descricao' => 'string',
        'data' => 'date:d/m/Y',
        //'ano' => 'date:Y',
        'ativo' => 'boolean',
    ];

    public $timestamps = false;

    protected static function booted() {
        static::creating(function ($model) {
            if(auth()->user()){ // esta assim pro conta do CRON
                $model->empresa_id = auth()->user()->empresa_id;
            }
        });

        static::addGlobalScope(new ScopeEmpresa());
    }

    public static function consultaFeriado($dataCompleta){
        $data = new DataHora($dataCompleta);
        return Feriado::whereData($data->dataInsert())->count() > 0 ? true:false;

    }
}
