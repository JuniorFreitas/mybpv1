<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\AvaliacaoResultado
 *
 * @property int $id
 * @property int|null $avaliacao_feedback_id
 * @property int|null $gestor_id
 * @property int|null $topico_id
 * @property string $plano_de_acao
 * @property string|null $responsavel
 * @property int $empresa_id
 * @property \Illuminate\Support\Carbon|null $inicio
 * @property \Illuminate\Support\Carbon|null $termino
 * @property string|null $status
 * @property array|null $dados_extras
 * @property-read \App\Models\AvaliacaoTopico|null $Topico
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoResultado newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoResultado newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoResultado query()
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoResultado whereAvaliacaoFeedbackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoResultado whereDadosExtras($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoResultado whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoResultado whereGestorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoResultado whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoResultado whereInicio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoResultado wherePlanoDeAcao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoResultado whereResponsavel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoResultado whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoResultado whereTermino($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AvaliacaoResultado whereTopicoId($value)
 * @mixin \Eloquent
 */
class AvaliacaoResultado extends Model
{
    use HasFactory, TenantTrait, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'avaliacoes_resultados';
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


    protected $table = "avaliacoes_resultados";

    protected $fillable = [
        'avaliacao_feedback_id',
        'gestor_id',
        'topico_id',
        'plano_de_acao',
        'responsavel',
        'empresa_id',
        'inicio',
        'termino',
        'status',
        'dados_extras',
    ];

    protected $casts = [
        'id' => 'int',
        'avaliacao_feedback_id' => 'int',
        'gestor_id' => 'int',
        'topico_id' => 'int',
        'plano_de_acao' => 'string',
        'responsavel' => 'string',
        'empresa_id' => 'int',
        'inicio' => 'date',
        'termino' => 'date',
        'status' => 'string',
        'dados_extras' => 'json',
    ];

    public $timestamps = false;

    const STATUS_DEFINIDO = 'Definido';

    public function getDadosExtrasAttribute($value)
    {
        return json_decode($value);
    }

    //Acessor ->data_fim
    public function getInicioAttribute($value)
    {
        if ($value) {
            $data = new DataHora($this->attributes['inicio']);
            return $data->dataCompleta();
        }
    }

    //Modificador ->data_inicio
    public function setInicioAttribute($value)
    {
        if ($value) {
            $dt = $value.' 00:00:00';
            $data = new DataHora($dt);
            $this->attributes['inicio'] = $data->dataInsert();
        }
    }

    //Acessor ->data_fim
    public function getTerminoAttribute($value)
    {
        if ($value) {
            $data = new DataHora($this->attributes['termino']);
            return $data->dataCompleta();
        }
    }

    //Modificador ->data_fim
    public function setTerminoAttribute($value)
    {
        if ($value) {
            $dt = $value.' 00:00:00';
            $data = new DataHora($dt);
            $this->attributes['termino'] = $data->dataInsert();
        }
    }

    public function Topico(){
        return $this->hasOne(AvaliacaoTopico::class, 'id', 'topico_id');
    }

}
