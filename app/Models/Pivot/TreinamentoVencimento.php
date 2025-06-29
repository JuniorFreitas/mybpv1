<?php

namespace App\Models\Pivot;

use App\Models\Arquivo;
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
 * @property string $data_vencimento
 * @property string|null $data_treinamento
 * @property string|null $numero_fat
 * @property int|null $arquivo_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoVencimento newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoVencimento newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoVencimento query()
 * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoVencimento whereDataTreinamento($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoVencimento whereDataVencimento($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoVencimento whereNumeroFat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoVencimento whereTreinamentoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoVencimento whereVencimentoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TreinamentoVencimento whereArquivoId($value)
 * @property-read Arquivo|null $arquivo
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

//    protected $dates = [
//        'data_vencimento',
//        'data_treinamento',
//    ];

    protected $fillable = [
        'vencimento_id',
        'treinamento_id',
        'data_vencimento',
        'data_treinamento',
        'numero_fat',
        'arquivo_id'
    ];
    protected $casts = [
        'vencimento_id' => 'int',
        'treinamento_id' => 'int',
        'data_vencimento' => 'string',
        'data_treinamento' => 'string',
        'numero_fat' => 'string',
        'arquivo_id' => 'int',
    ];

    public $timestamps = false;

    protected function serializeDate(DateTimeInterface $date)
    {
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
        } else {
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
        } else {
            $this->attributes['data_vencimento'] = null;
        }
    }

    public function arquivo()
    {
        return $this->belongsTo(Arquivo::class, 'arquivo_id', 'id');
    }
}
