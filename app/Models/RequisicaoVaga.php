<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;

/**
 * App\Models\RequisicaoVaga
 *
 * @property int $id
 * @property int|null $cliente_id
 * @property int $centro_custo_id
 * @property int $cargo_id
 * @property int|null $area_id
 * @property int $quantidade
 * @property string $tipo_contratacao
 * @property string $prioridade
 * @property bool $imediata
 * @property \Illuminate\Support\Carbon|null $previsao_inicio
 * @property string|null $solicitante
 * @property int $user_id
 * @property string|null $observacao
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $empresa_id
 * @property int|null $user_aprovacao_id
 * @property \Illuminate\Support\Carbon|null $data_aprovacao
 * @property string|null $obs_aprovacao
 * @property string|null $status_aprovacao
 * @property int|null $rh_aprovacao_id
 * @property \Illuminate\Support\Carbon|null $data_aprovacao_rh
 * @property string|null $obs_rh
 * @property string|null $status_aprovacao_rh
 * @property-read \App\Models\AreaEtiqueta|null $Area
 * @property-read \App\Models\Vaga|null $Cargo
 * @property-read \App\Models\CentroCusto|null $CentroCusto
 * @property-read \App\Models\Cliente|null $Cliente
 * @property-read \App\Models\User|null $GestorAprovacao
 * @property-read \App\Models\TipoContratacao|null $OutrasInformacoes
 * @property-read \App\Models\User|null $User
 * @property-read \App\Models\User|null $UserAprovacao
 * @property-read \App\Models\User|null $UserCadastrou
 * @property-read \App\Models\User|null $AprovacaoRh
 * @property-read \App\Models\RequisicaoVagaMovimentacao|null $Movimentacao
 * @property-read mixed $data_solicitacao
 * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga query()
 * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga whereAreaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga whereCargoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga whereCentroCustoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga whereClienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga whereDataAprovacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga whereImediata($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga whereObsAprovacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga whereObservacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga wherePrevisaoInicio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga wherePrioridade($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga whereQuantidade($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga whereSolicitante($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga whereStatusAprovacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga whereTipoContratacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga whereUserAprovacaoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVaga whereUserId($value)
 * @property int|null $aprovacao_extra_id
 * @property string|null $status_aprovacao_extra
 * @property string|null $obs_aprovacao_extra
 * @property \Illuminate\Support\Carbon|null $data_aprovacao_extra
 * @property-read \App\Models\User|null $AprovacaoExtra
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read mixed $created_at_formatado
 * @property-read mixed $updated_at_formatado
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVaga whereAprovacaoExtraId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVaga whereDataAprovacaoExtra($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVaga whereDataAprovacaoRh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVaga whereObsAprovacaoExtra($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVaga whereObsRh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVaga whereRhAprovacaoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVaga whereStatusAprovacaoExtra($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVaga whereStatusAprovacaoRh($value)
 * @mixin \Eloquent
 */
class RequisicaoVaga extends Model
{
    use HasFactory, TenantTrait, LogsActivity, HasActivitylogOptions;



    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }


    protected $fillable = [
        'cliente_id',
        'centro_custo_id',
        'cargo_id',
        'area_id',
        'quantidade',
        'tipo_contratacao',
        'prioridade',
        'imediata',
        'previsao_inicio',
        'solicitante',
        'user_id',
        'observacao',
        'empresa_id',
        'user_aprovacao_id',
        'data_aprovacao',
        'obs_aprovacao',
        'status_aprovacao',
        'aprovacao_extra_id',
        'status_aprovacao_extra',
        'obs_aprovacao_extra',
        'data_aprovacao_extra',
        'rh_aprovacao_id',
        'data_aprovacao_rh',
        'obs_rh',
        'status_aprovacao_rh',
    ];

    protected $casts = [
        'id' => 'int',
        'cliente_id' => 'int',
        'centro_custo_id' => 'int',
        'cargo_id' => 'int',
        'area_id' => 'int',
        'quantidade' => 'int',
        'tipo_contratacao' => 'string',
        'prioridade' => 'string',
        'imediata' => 'boolean',
        'previsao_inicio' => 'date:d/m/Y',
        'solicitante' => 'string',
        'user_id' => 'int',
        'observacao' => 'string',
        'empresa_id' => 'int',
        'user_aprovacao_id' => 'int',
        'data_aprovacao' => 'date:d/m/Y',
        'obs_aprovacao' => 'string',
        'status_aprovacao' => 'string',
        'aprovacao_extra_id' => 'int',
        'status_aprovacao_extra' => 'string',
        'obs_aprovacao_extra' => 'string',
        'data_aprovacao_extra' => 'datetime:d/m/Y à\s H:i:s',
        'rh_aprovacao_id' => 'int',
        'data_aprovacao_rh' => 'date:d/m/Y',
        'obs_rh' => 'string',
        'status_aprovacao_rh' => 'string',
    ];

    protected $appends = ['data_solicitacao', 'created_at_formatado', 'updated_at_formatado'];

    public function getDataSolicitacaoAttribute()
    {
        return (new DataHora($this->created_at))->dataCompleta();
    }

    public function getCreatedAtFormatadoAttribute()
    {
        return (new DataHora($this->created_at))->dataCompleta();
    }

    public function getUpdatedAtFormatadoAttribute()
    {
        return (new DataHora($this->updated_at))->dataCompleta();
    }

    public function setPrevisaoInicioAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($value);
            $this->attributes['previsao_inicio'] = $data->dataInsert();
        } else {
            $this->attributes['previsao_inicio'] = null;
        }
    }

    public function setDataAprovacaoAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($value);
            $this->attributes['data_aprovacao'] = $data->dataHoraInsert();
        } else {
            $this->attributes['data_aprovacao'] = null;
        }
    }

    public function Cliente()
    {
        return $this->hasOne(Cliente::class, 'id', 'cliente_id');
    }

    public function CentroCusto()
    {
        return $this->hasOne(CentroCusto::class, 'id', 'centro_custo_id');
    }

    public function Cargo()
    {
        return $this->hasOne(Vaga::class, 'id', 'cargo_id');
    }

    public function Area()
    {
        return $this->hasOne(AreaEtiqueta::class, 'id', 'area_id');
    }

    public function User()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function OutrasInformacoes()
    {
        return $this->hasOne(TipoContratacao::class, 'requisicao_vaga_id', 'id');
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

    public function AprovacaoExtra()
    {
        return $this->hasOne(User::class, 'id', 'aprovacao_extra_id');
    }

    public function AprovacaoRh()
    {
        return $this->hasOne(User::class, 'id', 'rh_aprovacao_id');
    }

    public function Movimentacao()
    {
        return $this->hasOne(RequisicaoVagaMovimentacao::class, 'id', 'id');
    }

    // Métodos auxiliares

    public function podeSerAprovadaPorGestor()
    {
        return is_null($this->user_aprovacao_id);
    }

    public function podeSerAprovadaPorExtra()
    {
        return $this->status_aprovacao === 'aprovado' && is_null($this->aprovacao_extra_id);
    }

    public function podeSerAprovadaPorRh()
    {
        return $this->status_aprovacao === 'aprovado' && $this->status_aprovacao_extra === 'aprovado' && is_null($this->rh_aprovacao_id);
    }

    public function temAprovacaoCompleta()
    {
        return $this->status_aprovacao === 'aprovado' &&
            $this->status_aprovacao_extra === 'aprovado' &&
            $this->status_aprovacao_rh === 'aprovado';
    }

    //Scopo de ClienteID (Empresa)
    //    protected static function booted()
    //    {
    //        static::creating(function ($model) {
    //            $model->user_id = auth()->id();
    //        });
    //
    //        static::updating(function ($model) {
    //            $model->user_id = auth()->id();
    //        });

    //        static::addGlobalScope(new ScopeClientesEmpresa);
    //    }
}
