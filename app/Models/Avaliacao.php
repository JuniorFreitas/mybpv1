<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\Avaliacao
 *
 * @property int $id
 * @property int $avaliacao_tipo_id
 * @property int $empresa_id
 * @property string $titulo
 * @property string|null $data_inicio_prazo
 * @property string|null $data_fim_prazo
 * @property string $status
 * @property bool $ativo
 * @property-read \App\Models\AvaliacaoTipo $AvaliacaoTipo
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|Avaliacao newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Avaliacao newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Avaliacao query()
 * @method static \Illuminate\Database\Eloquent\Builder|Avaliacao whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Avaliacao whereAvaliacaoTipoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Avaliacao whereDataFimPrazo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Avaliacao whereDataInicioPrazo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Avaliacao whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Avaliacao whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Avaliacao whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Avaliacao whereTitulo($value)
 * @mixin \Eloquent
 */
class Avaliacao extends Model
{
    use HasFactory, TenantTrait, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'avaliacoes';
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


    protected $table = "avaliacoes";

    protected $fillable = [
        'titulo',
        'avaliacao_tipo_id',
        'data_inicio_prazo',
        'data_fim_prazo',
        'empresa_id',
        'status',
        'ativo',
        'auto_avaliacao'
    ];

    protected $casts = [
        'id' => 'int',
        'avaliacao_tipo_id' => 'int',
        'titulo' => 'string',
        'data_inicio_prazo' => 'string',
        'data_fim_prazo' => 'string',
        'empresa_id' => 'int',
        'status' => 'string',
        'ativo' => 'boolean',
        'auto_avaliacao' => 'boolean'
    ];

    public $timestamps = false;

    const STATUS_AGUARDANDO_INICIO = 'Aguardando Inicio';
    const STATUS_ABERTA = 'Aberta';
    const STATUS_ENCERRADA = 'Encerrada';

    const LISTA_STATUS = [
        self::STATUS_AGUARDANDO_INICIO,
        self::STATUS_ABERTA,
        self::STATUS_ENCERRADA
    ];


    public function AvaliacaoTipo()
    {
        return $this->belongsTo(AvaliacaoTipo::class, 'avaliacao_tipo_id', 'id');
    }

    //Acessor ->data_inicio
    public function getDataInicioPrazoAttribute($value)
    {
        if ($value) {
            $data = new DataHora($this->attributes['data_inicio_prazo']);
            return $data->dataCompleta();
        }
    }

    //Modificador ->data_inicio
    public function setDataInicioPrazoAttribute($value)
    {
        if ($value) {
            $dt = $value . ' 00:00:00';
            $data = new DataHora($dt);
            $this->attributes['data_inicio_prazo'] = $data->dataHoraInsert();
        }
    }

    //Acessor ->data_fim
    public function getDataFimPrazoAttribute($value)
    {
        if ($value) {
            $data = new DataHora($this->attributes['data_fim_prazo']);
            return $data->dataCompleta();
        }
    }

    //Modificador ->data_fim
    public function setDataFimPrazoAttribute($value)
    {
        if ($value) {
            $dt = $value . ' 23:59:59';
            $data = new DataHora($dt);
            $this->attributes['data_fim_prazo'] = $data->dataHoraInsert();
        }
    }
}
