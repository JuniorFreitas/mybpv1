<?php

namespace App\Models\Pivot;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Relations\Pivot;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\Pivot\TreinamentoVencimento
 *
 * @property int $treinamento_id
 * @property int $vencimento_id
 * @property mixed $data_vencimento
 * @property mixed|null $data_treinamento
 * @property string|null $numero_fat
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Activitylog\Models\Activity[] $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pivot\TreinamentoVencimento newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pivot\TreinamentoVencimento newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pivot\TreinamentoVencimento query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pivot\TreinamentoVencimento whereDataTreinamento($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pivot\TreinamentoVencimento whereDataVencimento($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pivot\TreinamentoVencimento whereNumeroFat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pivot\TreinamentoVencimento whereTreinamentoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pivot\TreinamentoVencimento whereVencimentoId($value)
 * @mixin \Eloquent
 */
class TreinamentoVencimento extends Pivot
{
    use LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'treinamento_vencimento';
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

    protected $fillable = [
        'data_vencimento',
        'data_treinamento',
        'numero_fat'
    ];
    protected $casts = [
        'data_vencimento' => 'date:d/m/Y',
        'data_treinamento' => 'date:d/m/Y',
        'numero_fat' => 'string'
    ];

    protected function serializeDate(DateTimeInterface $date) {
        return $date->format('Y-m-d H:i:s');
    }

    public function getDataVencimentoAttribute($value)
    {
        if ($value) {
            $data = new DataHora($this->attributes['data_vencimento']);
            return $data->dataCompleta();
        }
        return null;
    }

    //Modificador ->data_vencimento
    public function setDataVencimentoAttribute($value)
    {
        if ($value) {
            $data = new DataHora($value);
            $this->attributes['data_vencimento'] = $data->dataInsert();
        }else{
            $this->attributes['data_vencimento'] = null;
        }
    }

    public function getDataTreinamentoAttribute($value)
    {
        if ($value) {
            $data = new DataHora($this->attributes['data_treinamento']);
            return $data->dataCompleta();
        }
    }

    //Modificador ->data_treinamento
    public function setDataTreinamentoAttribute($value)
    {
        if ($value) {
            $data = new DataHora($value);
            $this->attributes['data_treinamento'] = $data->dataInsert();
        }else{
            $this->attributes['data_vencimento'] = null;
        }
    }
}
