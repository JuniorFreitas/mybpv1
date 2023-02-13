<?php

namespace App\Models;

use App\Models\User;
use App\Tenant\Traits\TenantTrait;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;

/**
 * App\Models\FeriasPrevista
 *
 * @property int $id
 * @property int $cliente_id
 * @property int $colaborador_id
 * @property int $centro_custo_id
 * @property mixed $data_saida
 * @property int $qnt_dias
 * @property mixed $data_retorno
 * @property int $dias_saldo
 * @property int|null $user_id
 * @property string|null $solicitante
 * @property string|null $status
 * @property string|null $obs
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CentroCusto|null $CentroCusto
 * @property-read \App\Models\Cliente|null $Cliente
 * @property-read \App\Models\User|null $Funcionario
 * @property-read \App\Models\User|null $UserCadastrou
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista query()
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereCentroCustoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereClienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereColaboradorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereDataRetorno($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereDataSaida($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereDiasSaldo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereObs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereQntDias($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereSolicitante($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereUserId($value)
 * @mixin \Eloquent
 * @property-read \App\Models\User|null $Colaborador
 * @property int|null $user_aprovacao_id
 * @property mixed|null $data_aprovacao
 * @property string|null $obs_aprovacao
 * @property string|null $status_aprovacao
 * @property bool $tem_faltas
 * @property int|null $qnt_faltas
 * @property int|null $user_rh_id
 * @property string|null $resposta_rh
 * @property string|null $obs_rh
 * @property mixed|null $data_aprovacao_rh
 * @property int|null $empresa_id
 * @property-read User|null $GestorAprovacao
 * @property-read User|null $QuemAprovou
 * @property-read User|null $RhAprovacao
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereDataAprovacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereDataAprovacaoRh($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereObsAprovacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereObsRh($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereQntFaltas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereRespostaRh($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereStatusAprovacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereTemFaltas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereUserAprovacaoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereUserRhId($value)
 * @property int|null $gestor_id
 * @property string|null $periodo_aquisitivo
 * @property mixed|null $ultima_data
 * @property string|null $mes
 * @property int|null $periodo_aquisitivo_id
 * @property-read User|null $Empresa
 * @property-read \App\Models\PeriodoAquisitivo|null $PeriodoAquisitivo
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereGestorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereMes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista wherePeriodoAquisitivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista wherePeriodoAquisitivoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereUltimaData($value)
 * @property-read \App\Models\FeedbackCurriculo|null $Feedback
 * @property int $admissao_id
 * @property int $solicitante_id
 * @property string|null $obs_solicitante
 * @property \datetime $data_solicitacao
 * @property int|null $gestor_aprovacao_id
 * @property string|null $obs_gestor
 * @property string|null $status_aprovacao_gestor
 * @property \datetime|null $data_aprovacao_gestor
 * @property int|null $rh_aprovacao_id
 * @property string|null $status_aprovacao_rh
 * @property string|null $status_ferias
 * @property \datetime|null $data_status_ferias
 * @property int|null $ferias_prevista_id
 * @property bool $aprovado_via_script
 * @property-read \App\Models\Admissao|null $Admissao
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $Anexos
 * @property-read int|null $anexos_count
 * @property-read \App\Models\FeriasPrevista|null $FeriasPrevista
 * @property-read User|null $Solicitante
 * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereAdmissaoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereAprovadoViaScript($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereDataAprovacaoGestor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereDataSolicitacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereDataStatusFerias($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereFeriasPrevistaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereGestorAprovacaoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereObsGestor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereObsSolicitante($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereRhAprovacaoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereSolicitanteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereStatusAprovacaoGestor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereStatusAprovacaoRh($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ferias whereStatusFerias($value)
 */
class Ferias extends Model
{
    use HasFactory, TenantTrait;

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
        'aprovado_via_script'
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
        'aprovado_via_script' => 'boolean',
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
        self::FILTRO_ABERTO,
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
