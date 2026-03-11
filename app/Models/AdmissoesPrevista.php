<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use App\Tenant\Traits\TenantTrait;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;

/**
 * App\Models\AdmissoesPrevista
 *
 * @property int $id
 * @property int|null $cliente_id
 * @property int|null $colaborador_id
 * @property string|null $nome_pessoa
 * @property int $centro_custo_id
 * @property string $tipo_contrato
 * @property int $cargo_id
 * @property \Illuminate\Support\Carbon $data_admissao
 * @property float $salario
 * @property int|null $user_id
 * @property string|null $solicitante
 * @property string|null $obs
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $user_aprovacao_id
 * @property \Illuminate\Support\Carbon|null $data_aprovacao
 * @property string|null $obs_aprovacao
 * @property string|null $status_aprovacao
 * @property int|null $empresa_id
 * @property int|null $gestor_id
 * @property bool|null $filial
 * @property int|null $centro_custo_filial_id
 * @property int|null $rh_aprovacao_id
 * @property string|null $obs_rh
 * @property string|null $status_aprovacao_rh
 * @property \Illuminate\Support\Carbon|null $data_aprovacao_rh
 * @property bool $aprovado_via_script
 * @property int|null $quem_deletou_id
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $Anexos
 * @property-read int|null $anexos_count
 * @property-read \App\Models\Vaga|null $Cargo
 * @property-read \App\Models\CentroCusto|null $CentroCusto
 * @property-read \App\Models\CentroCustoFilial|null $CentroCustoFilial
 * @property-read \App\Models\Cliente|null $Cliente
 * @property-read \App\Models\User|null $Colaborador
 * @property-read \App\Models\User|null $GestorAprovacao
 * @property-read \App\Models\User|null $RhAprovacao
 * @property-read \App\Models\User|null $UserAprovacao
 * @property-read \App\Models\User|null $UserCadastrou
 * @property-read mixed $salario_format
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista query()
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereAprovadoViaScript($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereCargoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereCentroCustoFilialId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereCentroCustoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereClienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereColaboradorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereDataAdmissao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereDataAprovacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereDataAprovacaoRh($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereFilial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereGestorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereObs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereObsAprovacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereObsRh($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereQuemDeletouId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereRhAprovacaoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereSalario($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereSolicitante($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereStatusAprovacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereStatusAprovacaoRh($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereTipoContrato($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereUserAprovacaoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereUserId($value)
 * @property int|null $aprovacao_extra_id
 * @property string|null $status_aprovacao_extra
 * @property string|null $obs_aprovacao_extra
 * @property \Illuminate\Support\Carbon|null $data_aprovacao_extra
 * @property-read \App\Models\AprovacaoExtraConfig|null $AprovacaoExtra
 * @property-read \App\Models\User|null $UserAprovacaoExtra
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read mixed $aprovacao_extra_nome
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissoesPrevista whereAprovacaoExtraId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissoesPrevista whereDataAprovacaoExtra($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissoesPrevista whereNomePessoa($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissoesPrevista whereObsAprovacaoExtra($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissoesPrevista whereStatusAprovacaoExtra($value)
 * @mixin \Eloquent
 */
class AdmissoesPrevista extends Model
{
    use LogsActivity, HasActivitylogOptions, HasFactory, TenantTrait;

    protected static $logName = 'AdmissoesPrevista';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

    protected $fillable = [
        'colaborador_id',
        'nome_pessoa',
        'centro_custo_id',
        'filial',
        'centro_custo_filial_id',
        'tipo_contrato',
        'cargo_id',
        'data_admissao',
        'salario',
        'user_id',
        'solicitante',
        'obs',
        'user_aprovacao_id',
        'data_aprovacao',
        'obs_aprovacao',
        'status_aprovacao',
        'aprovacao_extra_id',
        'status_aprovacao_extra',
        'obs_aprovacao_extra',
        'data_aprovacao_extra',
        'empresa_id',
        'gestor_id',
        'rh_aprovacao_id',
        'obs_rh',
        'status_aprovacao_rh',
        'data_aprovacao_rh',
        'aprovado_via_script',
        'quem_deletou_id'
    ];

    protected $casts = [
        'id' => 'int',
        'cliente_id' => 'int',
        'colaborador_id' => 'int',
        'nome_pessoa' => 'string',
        'centro_custo_id' => 'int',
        'filial' => 'boolean',
        'centro_custo_filial_id' => 'int',
        'tipo_contrato' => 'string',
        'cargo_id' => 'int',
        'data_admissao' => 'date:d/m/Y',
        'salario' => 'float',
        'user_id' => 'int',
        'solicitante' => 'string',
        'obs' => 'string',
        'created_at' => 'datetime:d/m/Y à\s H:i:s',
        'updated_at' => 'datetime:d/m/Y à\s H:i:s',
        'user_aprovacao_id' => 'int',
        'data_aprovacao' => 'date:d/m/Y',
        'obs_aprovacao' => 'string',
        'status_aprovacao' => 'string',
        'aprovacao_extra_id' => 'int',
        'status_aprovacao_extra' => 'string',
        'obs_aprovacao_extra' => 'string',
        'data_aprovacao_extra' => 'datetime:d/m/Y à\s H:i:s',
        'empresa_id' => 'int',
        'gestor_id' => 'int',
        'rh_aprovacao_id' => 'int',
        'obs_rh' => 'string',
        'status_aprovacao_rh' => 'string',
        'data_aprovacao_rh' => 'datetime:d/m/Y à\s H:i:s',
        'aprovado_via_script' => 'boolean',
        'quem_deletou_id' => 'int'
    ];

    const STATUS_APROVADO = 'aprovado';
    const STATUS_REPROVADO = 'reprovado';

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    protected $appends = ['salario_format', 'aprovacao_extra_nome'];

    public function setDataAdmissaoAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($value);
            $this->attributes['data_admissao'] = $data->dataInsert();
        } else {
            $this->attributes['data_admissao'] = null;
        }
    }

    public function getSalarioFormatAttribute()
    {
        return number_format($this->attributes['salario'], 2, ',', '.');
    }

    public function getAprovacaoExtraNomeAttribute()
    {
        return $this->UserAprovacaoExtra ? $this->UserAprovacaoExtra->nome : '';
    }

    public function setSalarioAttribute($value)
    {
        $this->attributes['salario'] = Sistema::DinheiroInsert($value);
    }

    public function Cliente()
    {
        return $this->hasOne(Cliente::class, 'id', 'cliente_id');
    }

    public function Colaborador()
    {
        return $this->hasOne(User::class, 'id', 'colaborador_id');
    }

    public function CentroCusto()
    {
        return $this->hasOne(CentroCusto::class, 'id', 'centro_custo_id');
    }

    public function CentroCustoFilial()
    {
        return $this->hasOne(CentroCustoFilial::class, 'id', 'centro_custo_filial_id');
    }

    public function UserCadastrou()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function Cargo()
    {
        return $this->hasOne(Vaga::class, 'id', 'cargo_id');
    }

    public function GestorAprovacao()
    {
        return $this->hasOne(User::class, 'id', 'gestor_id');
    }


    public function UserAprovacao()
    {
        return $this->hasOne(User::class, 'id', 'user_aprovacao_id');
    }

    public function UserAprovacaoExtra()
    {
        return $this->hasOne(User::class, 'id', 'aprovacao_extra_id');
    }

    public function AprovacaoExtra()
    {
        return $this->belongsTo(AprovacaoExtraConfig::class, 'aprovacao_extra_id');
    }

    public function RhAprovacao()
    {
        return $this->hasOne(User::class, 'id', 'rh_aprovacao_id');
    }

    public function Anexos()
    {
        return $this->belongsToMany(Arquivo::class, 'admissoes_previstas_anexos', 'admissoes_prevista_id', 'arquivo_id');
    }
}
