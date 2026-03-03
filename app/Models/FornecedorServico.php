<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;

/**
 * App\Models\FornecedorServico
 *
 * @property int $id
 * @property int $fornecedor_id
 * @property int|null $tipo_servico_fornecedor_id
 * @property string|null $vencimento quando for utilizado para fornecedor
 * @property \Illuminate\Support\Carbon|null $data_inicio
 * @property \Illuminate\Support\Carbon|null $data_encerramento
 * @property string|null $escopo
 * @property string|null $valor
 * @property string|null $tipo_faturamento
 * @property string|null $status
 * @property string|null $feedback
 * @property bool $ativo
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $Anexos
 * @property-read int|null $anexos_count
 * @property-read \App\Models\TipoServico|null $TipoServico
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|FornecedorServico newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FornecedorServico newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FornecedorServico query()
 * @method static \Illuminate\Database\Eloquent\Builder|FornecedorServico whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FornecedorServico whereDataEncerramento($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FornecedorServico whereDataInicio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FornecedorServico whereEscopo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FornecedorServico whereFeedback($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FornecedorServico whereFornecedorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FornecedorServico whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FornecedorServico whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FornecedorServico whereTipoFaturamento($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FornecedorServico whereTipoServicoFornecedorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FornecedorServico whereValor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FornecedorServico whereVencimento($value)
 * @mixin \Eloquent
 */
class FornecedorServico extends Model
{
    use HasFactory, LogsActivity, HasActivitylogOptions;

    protected static $logFillable = true;
    protected static $logName = 'fornecedor_servico';
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

    public $table = 'fornecedor_servico';

    protected $fillable = [
        'fornecedor_id',
        'tipo_servico_fornecedor_id',
        'vencimento',
        'data_inicio',
        'data_encerramento',
        'escopo',
        'valor',
        'tipo_faturamento',
        'status',
        'feedback',
        'ativo',
    ];

    protected $casts = [
        'id' => 'int',
        'fornecedor_id' => 'int',
        'tipo_servico_fornecedor_id' => 'int',
        'vencimento' => 'string',
        'data_inicio' => 'date:d/m/Y',
        'data_encerramento' => 'date:d/m/Y',
        'escopo' => 'string',
        'valor' => 'string',
        'tipo_faturamento' => 'string',
        'status' => 'string',
        'feedback' => 'string',
        'ativo' => 'boolean',
    ];

    const DE_ZERO_A_QUINHENTOS = "R$ 0,00 a R$ 500,00";
    const DE_QUINHENTOS_A_MIL = "R$ 500,00 a R$ 1.000,00";
    const ACIMA_DE_MIL = "Acima de R$ 1.000,00";

    const STATUS_INICIADO = "Iniciado";
    const STATUS_CONCLUIDO = "Concluido";
    const STATUS_NAO_INICIADO = "Não iniciado";

    const FEEDBACK_QUALIFICADO = "Qualificado";
    const FEEDBACK_NAO_QUALIFICADO = "Não Qualificado";

    const TIPO_FATURAMENTO_UNICO = "Único";
    const TIPO_FATURAMENTO_POR_EXECUCACAO = "Por execução";

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


    public function TipoServico()
    {
        return $this->hasOne(TipoServico::class, 'id', 'tipo_servico_id');
    }

    public function Anexos()
    {
        return $this->belongsToMany(Arquivo::class, 'fornecedor_servico_anexos', 'fornecedor_servico_id', 'arquivo_id');
    }
}
