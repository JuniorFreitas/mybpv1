<?php

namespace App\Models;

use App\Models\User;
use App\Tenant\Traits\TenantTrait;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use MasterTag\DataHora;

/**
 * App\Models\Ferias
 *
 * @property int $id
 * @property int $empresa_id
 * @property int $admissao_id
 * @property int $periodo_aquisitivo_id
 * @property mixed $data_saida
 * @property mixed $data_retorno
 * @property mixed $ultima_data
 * @property int|null $qnt_dias
 * @property int|null $dias_saldo
 * @property bool $tem_faltas
 * @property int|null $qnt_faltas
 * @property int $solicitante_id
 * @property string|null $obs_solicitante
 * @property \datetime $data_solicitacao
 * @property int|null $gestor_id
 * @property int|null $gestor_aprovacao_id
 * @property string|null $obs_gestor
 * @property string|null $status_aprovacao_gestor
 * @property \datetime|null $data_aprovacao_gestor
 * @property int|null $rh_aprovacao_id
 * @property string|null $obs_rh
 * @property string|null $status_aprovacao_rh
 * @property \datetime|null $data_aprovacao_rh
 * @property string|null $status_ferias
 * @property \datetime|null $data_status_ferias
 * @property int|null $ferias_prevista_id
 * @property bool $aprovado_via_script
 * @property \datetime|null $created_at
 * @property \datetime|null $updated_at
 * @property int|null $quem_deletou_id
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property bool $abono_pecuniario
 * @property bool $adiantamento_decimo_terceiro
 * @property-read \App\Models\Admissao|null $Admissao
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $Anexos
 * @property-read int|null $anexos_count
 * @property-read User|null $Empresa
 * @property-read \App\Models\FeriasPrevista|null $FeriasPrevista
 * @property-read User|null $Gestor
 * @property-read User|null $GestorAprovacao
 * @property-read \App\Models\PeriodoAquisitivo|null $PeriodoAquisitivo
 * @property-read User|null $RhAprovacao
 * @property-read User|null $Solicitante
 * @method static \Illuminate\Database\Eloquent\Builder|Ferias newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ferias newQuery()
 * @method static \Illuminate\Database\Query\Builder|Ferias onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Ferias query()
 * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereAbonoPecuniario($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereAdiantamentoDecimoTerceiro($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereAdmissaoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereAprovadoViaScript($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereDataAprovacaoGestor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereDataAprovacaoRh($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereDataRetorno($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereDataSaida($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereDataSolicitacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereDataStatusFerias($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereDiasSaldo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereFeriasPrevistaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereGestorAprovacaoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereGestorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereObsGestor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereObsRh($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereObsSolicitante($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ferias wherePeriodoAquisitivoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereQntDias($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereQntFaltas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereQuemDeletouId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereRhAprovacaoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereSolicitanteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereStatusAprovacaoGestor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereStatusAprovacaoRh($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereStatusFerias($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereTemFaltas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereUltimaData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Ferias withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Ferias withoutTrashed()
 * @mixin \Eloquent
 */
class Ferias extends Model
{
    use HasFactory, TenantTrait, SoftDeletes;

    protected $fillable = [
        'empresa_id',
        'admissao_id',
        'periodo_aquisitivo_id',
        'data_saida',
        'data_retorno',
        'ultima_data',
        'qnt_dias',
        'dias_saldo',
        'tem_faltas',
        'qnt_faltas',
        'solicitante_id',
        'obs_solicitante',
        'data_solicitacao',
        'gestor_aprovacao_id',
        'gestor_id',
        'obs_gestor',
        'status_aprovacao_gestor',
        'data_aprovacao_gestor',
        'rh_aprovacao_id',
        'obs_rh',
        'status_aprovacao_rh',
        'data_aprovacao_rh',
        'status_ferias',
        'data_status_ferias',
        'ferias_prevista_id',
        'aprovado_via_script',
        'abono_pecuniario',
        'adiantamento_decimo_terceiro',
        'quem_deletou_id'
    ];

    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'admissao_id' => 'int',
        'periodo_aquisitivo_id' => 'int',
        'data_saida' => 'date:d/m/Y',
        'data_retorno' => 'date:d/m/Y',
        'ultima_data' => 'date:d/m/Y',
        'qnt_dias' => 'int',
        'dias_saldo' => 'int',
        'tem_faltas' => 'boolean',
        'qnt_faltas' => 'int',
        'solicitante_id' => 'int',
        'obs_solicitante' => 'string',
        'data_solicitacao' => 'datetime:d/m/Y à\s H:i:s',
        'gestor_aprovacao_id' => 'int',
        'gestor_id' => 'int',
        'obs_gestor' => 'string',
        'status_aprovacao_gestor' => 'string',
        'data_aprovacao_gestor' => 'datetime:d/m/Y à\s H:i:s',
        'rh_aprovacao_id' => 'int',
        'obs_rh' => 'string',
        'status_aprovacao_rh' => 'string',
        'data_aprovacao_rh' => 'datetime:d/m/Y à\s H:i:s',
        'status_ferias' => 'string',
        'data_status_ferias' => 'datetime:d/m/Y à\s H:i:s',
        'ferias_prevista_id' => 'int',
        'quem_deletou_id' => 'int',
        'aprovado_via_script' => 'boolean',
        'abono_pecuniario' => 'boolean',
        'adiantamento_decimo_terceiro' => 'boolean',
        'created_at' => 'datetime:d/m/Y à\s H:i:s',
        'updated_at' => 'datetime:d/m/Y à\s H:i:s'
    ];

    const STATUS_APROVADO = 'aprovado';
    const STATUS_REPROVADO = 'reprovado';

    const FILTRO_ABERTO = 'Aberto';
    const FILTRO_REPROVADO_GESTOR = 'Reprovado pelo Gestor';
    const FILTRO_REPROVADO_RH = 'Reprovado pelo Rh';
    const FILTRO_APROVADO_GESTOR = 'Aprovado pelo Gestor';
    const FILTRO_APROVADO_RH = 'Aprovado pelo Rh';

    const LISTA_FILTRO_APROVACAO = [
        self::FILTRO_ABERTO,
        self::FILTRO_REPROVADO_GESTOR,
        self::FILTRO_REPROVADO_RH,
        self::FILTRO_APROVADO_GESTOR,
        self::STATUS_REPROVADO
    ];


    const LISTA_STATUS_APROVACAO = [
        self::STATUS_APROVADO,
        self::STATUS_REPROVADO
    ];

    const STATUS_GOZANDO = 'gozando';
    const STATUS_GOZADA = 'gozada';
    const STATUS_AGUARDANDO = 'aguardando';
    const STATUS_CANCELADA = 'cancelada';

    const LISTA_STATUS_FERIAS = [
        self::STATUS_GOZANDO,
        self::STATUS_GOZADA,
        self::STATUS_AGUARDANDO,
        self::STATUS_CANCELADA
    ];

    const LISTA_RELATORIO_VENCIMENTO_FERIAS = [
        self::STATUS_AGUARDANDO,
        self::STATUS_GOZANDO
    ];

    public function setDataSaidaAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($value);
            $this->attributes['data_saida'] = $data->dataInsert();
        } else {
            $this->attributes['data_saida'] = null;
        }
    }

    public function getDataSaidaAttribute($value)
    {
        $data = new DataHora($this->attributes['data_saida']);
        return $data->dataCompleta();
    }

    public function getUltimaDataAttribute($value)
    {
        $data = new DataHora($this->attributes['ultima_data']);
        return $data->dataCompleta();
    }

    public function setDataRetornoAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($value);
            $this->attributes['data_retorno'] = $data->dataInsert();
        } else {
            $this->attributes['data_retorno'] = null;
        }
    }

    public function getDataRetornoAttribute($value)
    {
        $data = new DataHora($this->attributes['data_retorno']);
        return $data->dataCompleta();
    }

    public function getDataAprovacaoGestorAttribute($value)
    {
        $data = new DataHora($this->attributes['data_aprovacao_gestor']);
        return $data->dataHoraCompleta();
    }

    public function getDataAprovacaoRhAttribute($value)
    {
        $data = new DataHora($this->attributes['data_aprovacao_rh']);
        return $data->dataHoraCompleta();
    }

    public function getDataStatusFeriasAttribute($value)
    {
        $data = new DataHora($this->attributes['data_status_ferias']);
        return $data->dataHoraCompleta();
    }

    public function Empresa()
    {
        return $this->hasOne(User::class, 'id', 'empresa_id');
    }

    public function Admissao()
    {
        return $this->hasOne(Admissao::class, 'id', 'admissao_id');
    }

    public function PeriodoAquisitivo()
    {
        return $this->hasOne(PeriodoAquisitivo::class, 'id', 'periodo_aquisitivo_id');
    }

    public function Solicitante()
    {
        return $this->hasOne(User::class, 'id', 'solicitante_id');
    }

    public function GestorAprovacao()
    {
        return $this->hasOne(User::class, 'id', 'gestor_aprovacao_id');
    }

    public function Gestor()
    {
        return $this->hasOne(User::class, 'id', 'gestor_id');
    }

    public function RhAprovacao()
    {
        return $this->hasOne(User::class, 'id', 'rh_aprovacao_id');
    }

    public function FeriasPrevista()
    {
        return $this->hasOne(FeriasPrevista::class, 'id', 'ferias_prevista_id');
    }

    public function Anexos()
    {
        return $this->belongsToMany(Arquivo::class, 'documento_legais_contratos_anexos', 'id', 'arquivo_id');
    }
}
