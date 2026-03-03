<?php

namespace App\Models;

use App\Scopes\ScopeEmpresa;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;

/**
 * App\Models\Feriado
 *
 * @property int $id
 * @property int $empresa_id
 * @property string $descricao
 * @property \Illuminate\Support\Carbon $data
 * @property bool $ativo
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|Feriado newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Feriado newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Feriado query()
 * @method static \Illuminate\Database\Eloquent\Builder|Feriado whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feriado whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feriado whereDescricao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feriado whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feriado whereId($value)
 * @mixin \Eloquent
 */
class Feriado extends Model {
    use LogsActivity, HasActivitylogOptions;
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
