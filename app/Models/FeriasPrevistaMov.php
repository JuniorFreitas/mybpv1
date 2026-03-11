<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use DateTimeInterface;

/**
 * App\Models\FeriasPrevistaMov
 *
 * @property int $id
 * @property int $colaborador_id
 * @property int $dias_saldo
 * @property int|null $empresa_id
 * @property int|null $ultimo_periodo_aquisitivo_id
 * @property string|null $ultima_data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Curriculo|null $Colaborador
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FeriasPrevistaDados> $FeriasPrevistaDados
 * @property-read int|null $ferias_prevista_dados_count
 * @property-read \App\Models\FeriasPrevistaDados|null $FeriasPrevistaDadosUltimo
 * @property-read \App\Models\PeriodoAquisitivo|null $PeriodoAquisitivo
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaMov newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaMov newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaMov query()
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaMov whereColaboradorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaMov whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaMov whereDiasSaldo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaMov whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaMov whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaMov whereUltimaData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaMov whereUltimoPeriodoAquisitivoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaMov whereUpdatedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @mixin \Eloquent
 */
class FeriasPrevistaMov extends Model
{
    use LogsActivity, HasActivitylogOptions, HasFactory, TenantTrait;

    protected static $logName = 'FeriasPrevistaMov';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

    protected $fillable = [
        'colaborador_id',
        'dias_saldo',
        'empresa_id',
        'ultimo_periodo_aquisitivo_id',
        'ultima_data',
    ];

    protected $casts = [
        'id' => 'int',
        'colaborador_id' => 'int',
        'dias_saldo' => 'int',
        'empresa_id' => 'int',
        'ultimo_periodo_aquisitivo_id' => 'int',
        'ultima_data' => 'string',
    ];


    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function getUltimaDataAttribute($value)
    {
        if ($value) {
            $data = new DataHora($this->attributes['ultima_data']);
            return $data->dataCompleta();
        }
    }

    //Modificador ->data_fim
    public function setUltimaDataAttribute($value)
    {
        if ($value) {
            $data = new DataHora($value);
            $this->attributes['ultima_data'] = $data->dataInsert();
        }
    }

    public function Colaborador()
    {
        return $this->hasOne(Curriculo::class, 'id', 'colaborador_id');
    }

    public function FeriasPrevistaDados()
    {
        return $this->hasMany(FeriasPrevistaDados::class, 'ferias_prevista_id', 'id');
    }

    public function FeriasPrevistaDadosUltimo()
    {
        return $this->hasOne(FeriasPrevistaDados::class, 'ferias_prevista_id', 'id')->latest();
    }

    public function PeriodoAquisitivo()
    {
        return $this->hasOne(PeriodoAquisitivo::class, 'id', 'ultimo_periodo_aquisitivo_id');
    }

}
