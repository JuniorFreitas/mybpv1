<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\ServicosCliente
 *
 * @property int $id
 * @property int|null $cliente_id
 * @property int|null $servico_id
 * @property mixed $data_inicio
 * @property mixed $data_encerramento
 * @property string|null $escopo
 * @property float $valor
 * @property string $tipo_faturamento
 * @property string $status
 * @property string|null $feedback
 * @property bool $ativo
 * @property string $tipo_contrato
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $Anexos
 * @property-read int|null $anexos_count
 * @property-read \App\Models\Cliente|null $Cliente
 * @property-read \App\Models\Servico|null $Servico
 * @property-read \App\Models\TipoServico|null $TipoServico
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|ServicosCliente newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ServicosCliente newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ServicosCliente query()
 * @method static \Illuminate\Database\Eloquent\Builder|ServicosCliente whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServicosCliente whereClienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServicosCliente whereDataEncerramento($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServicosCliente whereDataInicio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServicosCliente whereEscopo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServicosCliente whereFeedback($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServicosCliente whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServicosCliente whereServicoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServicosCliente whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServicosCliente whereTipoContrato($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServicosCliente whereTipoFaturamento($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServicosCliente whereValor($value)
 * @mixin \Eloquent
 */
class ServicosCliente extends Model
{
    use HasFactory;

    use LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'servico_cliente';
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

    protected $fillable = [
        'cliente_id',
        'servico_id',
        'data_inicio',
        'data_encerramento',
        'escopo',
        'valor',
        'tipo_faturamento',
        'status',
        'feedback',
        'ativo',
        'tipo_contrato',
    ];
    protected $casts = [
        'id' => 'int',
        'cliente_id' => 'int',
        'servico_id' => 'int',
        'data_inicio' => 'date:d/m/Y',
        'data_encerramento' => 'date:d/m/Y',
        'escopo' => 'string',
        'valor' => 'float',
        'tipo_faturamento' => 'string',
        'status' => 'string',
        'feedback' => 'string',
        'ativo' => 'boolean',
        'tipo_contrato' => 'string',
    ];

//    protected $appends = ['ValorFormat'];


//    public function getPeriodoFormatAttribute()
//    {
//        $dataInicio = (new DataHora($this->periodo_inicio));
//        $dataFim = (new DataHora($this->periodo_fim));
//        return $dataInicio->dataCompleta() . ' às ' . $dataInicio->hora() . ':' . $dataInicio->minuto() . ' até ' . $dataFim->dataCompleta() . ' às ' . $dataFim->hora() . ':' . $dataFim->minuto();
//    }

    const TIPO_CONTRATO_FIXO = 'FIXO';
    const TIPO_CONTRATO_SPOT = 'SPOT';

    //Modificador ->data_inicio
    public function setDataInicioAttribute($value)
    {
        $data = new DataHora($value);
        $this->attributes['data_inicio'] = $data->dataInsert();
    }

    //Modificador ->data_encerramento
    public function setDataEncerramentoAttribute($value)
    {
        $data = new DataHora($value);
        $this->attributes['data_encerramento'] = $data->dataInsert();
    }

    //Modificador ->vale_transporte_linhadois
    public function setValorAttribute($value)
    {
        $this->attributes['valor'] = Sistema::DinheiroInsert($value);
    }

    public function getValorAttribute($value)
    {
        return number_format($value, 2, ',', '.');
    }

    public function Cliente()
    {
        return $this->hasOne(Cliente::class, 'id', 'cliente_id');
    }

    public function TipoServico()
    {
        return $this->hasOne(TipoServico::class, 'id', 'tipo_servico_id');
    }

    public function Servico()
    {
        return $this->hasOne(Servico::class, 'id', 'servico_id');
    }

    public function Anexos()
    {
        return $this->belongsToMany(Arquivo::class, 'cliente_servicos_imagens', 'servicos_cliente_id', 'arquivo_id');
    }

}
