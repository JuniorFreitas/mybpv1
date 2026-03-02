<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;

/**
 * App\Models\IntermitenteProrrogacao
 *
 * @property int $id
 * @property int $intermitente_id
 * @property string $data_inicio
 * @property string $data_fim
 * @property string $solicitante
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteProrrogacao newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteProrrogacao newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteProrrogacao query()
 * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteProrrogacao whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteProrrogacao whereDataFim($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteProrrogacao whereDataInicio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteProrrogacao whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteProrrogacao whereIntermitenteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteProrrogacao whereSolicitante($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteProrrogacao whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class IntermitenteProrrogacao extends Model
{
    use HasFactory, LogsActivity, HasActivitylogOptions;


    protected static $logFillable = true;
    protected static $logName = 'intermitente_prorrogacaos';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    public function getDescriptionForEvent(string $eventName)
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = "";
    }

    protected $fillable = [
        'intermitente_id',
        'data_inicio',
        'data_fim',
        'solicitante',
    ];

    protected $casts = [
        'id' => 'int',
        'intermitente_id' => 'int',
        'data_inicio' => 'string',
        'data_fim' => 'string',
        'solicitante' => 'string',
    ];

    public function getDataInicioAttribute($value)
    {
        if ($value) {
            $data = new DataHora($this->attributes['data_inicio']);
            return $data->dataCompleta();
        }
    }

    public function setDataInicioAttribute($value)
    {
        if ($value) {
            $data = new DataHora($value);
            $this->attributes['data_inicio'] = $data->dataInsert();
        }
    }

    public function getDataFimAttribute($value)
    {
        if ($value) {
            $data = new DataHora($this->attributes['data_fim']);
            return $data->dataCompleta();
        }
    }

    //Modificador ->data_fim
    public function setDataFimAttribute($value)
    {
        if ($value) {
            $data = new DataHora($value);
            $this->attributes['data_fim'] = $data->dataInsert();
        }
    }

}
