<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;

/**
 * App\Models\CurriculoExperiencia
 *
 * @property int $id
 * @property int $curriculo_id
 * @property string $empresa
 * @property string $cargo
 * @property string $principais_atv
 * @property string|null $data_inicio
 * @property string|null $data_fim
 * @property string|null $referencia_nome
 * @property string|null $referencia_telefone
 * @property-read \App\Models\Curriculo|null $Curriculo
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|CurriculoExperiencia newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CurriculoExperiencia newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CurriculoExperiencia query()
 * @method static \Illuminate\Database\Eloquent\Builder|CurriculoExperiencia whereCargo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CurriculoExperiencia whereCurriculoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CurriculoExperiencia whereDataFim($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CurriculoExperiencia whereDataInicio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CurriculoExperiencia whereEmpresa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CurriculoExperiencia whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CurriculoExperiencia wherePrincipaisAtv($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CurriculoExperiencia whereReferenciaNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CurriculoExperiencia whereReferenciaTelefone($value)
 * @mixin \Eloquent
 */
class CurriculoExperiencia extends Model
{
    use HasFactory, LogsActivity, HasActivitylogOptions;

    protected static $logFillable = true;
    protected static $logName = 'curriculo_experiencia';
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

    public function usesTimestamps(): bool
    {
        return false;
    }

    protected $table = 'curriculo_experiencias';
    protected $fillable = [
        'curriculo_id',
        'empresa',
        'cargo',
        'principais_atv',
        'data_inicio',
        'data_fim',
        'referencia_nome',
        'referencia_telefone',
    ];
    protected $casts = [
        'id' => 'int',
        'curriculo_id' => 'int',
        'empresa' => 'string',
        'cargo' => 'string',
        'principais_atv' => 'string',
        'data_inicio' => 'string',
        'data_fim' => 'string',
        'referencia_nome' => 'string',
        'referencia_telefone' => 'string',
    ];

    public function Curriculo()
    {
        return $this->hasOne(Curriculo::class, 'id', 'curriculo');
    }

    //Acessor ->data_inicio
    public function getDataInicioAttribute($value)
    {
        $data = new DataHora($this->attributes['data_inicio']);
        return $data->dataCompleta();
    }

    //Modificador ->data_inicio
    public function setDataInicioAttribute($value)
    {
        $data = new DataHora($value);
        $this->attributes['data_inicio'] = $data->dataInsert();
    }

    //Acessor ->data_fim
    public function getDataFimAttribute($value)
    {
        $data = new DataHora($this->attributes['data_fim']);
        return $data->dataCompleta();
    }

    //Modificador ->data_fim
    public function setDataFimAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($value);
            $this->attributes['data_fim'] = $data->dataInsert();
        } else {
            $this->attributes['data_fim'] = null;
        }
    }

}
