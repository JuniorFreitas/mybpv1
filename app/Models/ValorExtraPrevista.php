<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use App\Scopes\ScopeClientesEmpresa;
use App\Tenant\Traits\TenantTrait;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;

/**
 * App\Models\ValorExtraPrevista
 *
 * @property int $id
 * @property int|null $cliente_id
 * @property int|null $colaborador_id
 * @property int $centro_custo_id
 * @property string $tipo
 * @property float|null $periodo_dias
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
 * @property int|null $filial
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
 * @property-read \App\Models\CentroCusto|null $CentroCusto
 * @property-read \App\Models\CentroCustoFilial|null $CentroCustoFilial
 * @property-read \App\Models\Cliente|null $Cliente
 * @property-read \App\Models\User|null $Colaborador
 * @property-read \App\Models\User|null $GestorAprovacao
 * @property-read \App\Models\User|null $RhAprovacao
 * @property-read \App\Models\User|null $UserAprovacao
 * @property-read \App\Models\User|null $UserCadastrou
 * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista query()
 * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereAprovadoViaScript($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereCentroCustoFilialId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereCentroCustoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereClienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereColaboradorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereDataAprovacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereDataAprovacaoRh($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereFilial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereGestorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereObs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereObsAprovacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereObsRh($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista wherePeriodoDias($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereQuemDeletouId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereRhAprovacaoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereSolicitante($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereStatusAprovacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereStatusAprovacaoRh($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereUserAprovacaoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereUserId($value)
 * @mixin \Eloquent
 */
class ValorExtraPrevista extends Model
{
    use LogsActivity, HasActivitylogOptions, TenantTrait;

    protected static $logName = 'ValorExtraPrevista';

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
        'centro_custo_id',
        'centro_custo_filial_id',
        'tipo',
        'periodo_dias',
        'user_id',
        'solicitante',
        'obs',
        'user_aprovacao_id',
        'data_aprovacao',
        'obs_aprovacao',
        'status_aprovacao',
        'empresa_id',
        'gestor_id',
        'rh_aprovacao_id',
        'obs_rh',
        'status_aprovacao_rh',
        'data_aprovacao_rh',
        'aprovado_via_script',
        'quem_deletou_id',
        'aprovacao_extra_id',
        'status_aprovacao_extra',
        'obs_aprovacao_extra',
        'data_aprovacao_extra',
    ];

    protected $casts = [
        'id' => 'int',
        'cliente_id' => 'int',
        'colaborador_id' => 'int',
        'centro_custo_id' => 'int',
        'centro_custo_filial_id' => 'int',
        'tipo' => 'string',
        'periodo_dias' => 'float',
        'user_id' => 'int',
        'solicitante' => 'string',
        'obs' => 'string',
        'created_at' => 'datetime:d/m/Y à\s H:i:s',
        'updated_at' => 'datetime:d/m/Y à\s H:i:s',
        'user_aprovacao_id' => 'int',
        'data_aprovacao' => 'date:d/m/Y',
        'obs_aprovacao' => 'string',
        'status_aprovacao' => 'string',
        'empresa_id' => 'int',
        'gestor_id' => 'int',
        'rh_aprovacao_id' => 'int',
        'obs_rh' => 'string',
        'status_aprovacao_rh' => 'string',
        'data_aprovacao_rh' => 'datetime:d/m/Y à\s H:i:s',
        'aprovado_via_script' => 'boolean',
        'quem_deletou_id' => 'int',
        'aprovacao_extra_id' => 'int',
        'status_aprovacao_extra' => 'string',
        'obs_aprovacao_extra' => 'string',
        'data_aprovacao_extra' => 'datetime:d/m/Y à\s H:i:s',
    ];

    const STATUS_APROVADO = 'aprovado';
    const STATUS_REPROVADO = 'reprovado';

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
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

    public function GestorAprovacao()
    {
        return $this->hasOne(User::class, 'id', 'gestor_id');
    }

    public function UserAprovacao()
    {
        return $this->hasOne(User::class, 'id', 'user_aprovacao_id');
    }

    public function RhAprovacao()
    {
        return $this->hasOne(User::class, 'id', 'rh_aprovacao_id');
    }

    public function AprovacaoExtra()
    {
        return $this->hasOne(User::class, 'id', 'aprovacao_extra_id');
    }

    public function Anexos()
    {
        return $this->belongsToMany(Arquivo::class, 'valor_extra_previstas_anexos', 'valor_extra_prevista_id', 'arquivo_id');
    }
}
