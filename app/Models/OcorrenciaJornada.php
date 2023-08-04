<?php

namespace App\Models;

use App\Scopes\ScopeEmpresa;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\OcorrenciaJornada
 *
 * @property int $id
 * @property int|null $empresa_id
 * @property string $descricao
 * @property bool $trabalhado
 * @property bool $conta_horas
 * @property bool $ativo
 * @property \datetime|null $created_at
 * @property \datetime|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|OcorrenciaJornada newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OcorrenciaJornada newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OcorrenciaJornada query()
 * @method static \Illuminate\Database\Eloquent\Builder|OcorrenciaJornada whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OcorrenciaJornada whereContaHoras($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OcorrenciaJornada whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OcorrenciaJornada whereDescricao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OcorrenciaJornada whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OcorrenciaJornada whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OcorrenciaJornada whereTrabalhado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OcorrenciaJornada whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OcorrenciaJornada extends Model
{
    use HasFactory, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'OcorrenciasJornadas';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = "";
    }

    protected $table = 'ocorrencias_jornada';

    protected $fillable = [
        'descricao',
        'trabalhado',
        'conta_horas',
        'ativo',
    ];

    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'descricao' => 'string',
        'trabalhado' => 'boolean',
        'conta_horas' => 'boolean',
        'ativo' => 'boolean',
        'created_at' => 'datetime:d/m/Y à\s H:i:s',
        'updated_at' => 'datetime:d/m/Y à\s H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date) {
        return $date->format('Y-m-d H:i:s');
    }

    public $timestamps=true;

    const DIA_TRABALHADO=4;
    const FALTA=5;
    const FERIADO=7;
    const FOLGA=8;
    const JORNADA_EXTRA=10;

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->empresa_id = auth()->user() ? auth()->user()->empresa_id :null; // por causa do seed
        });

        static::updating(function ($model) {
            $model->empresa_id = auth()->user() ? auth()->user()->empresa_id:null; // por causa do seed
        });

        static::addGlobalScope(new ScopeEmpresa());
    }

    public static function Fixas(){
        return [
            OcorrenciaJornada::DIA_TRABALHADO,
            OcorrenciaJornada::FOLGA,
            OcorrenciaJornada::JORNADA_EXTRA,
        ];
    }
}
