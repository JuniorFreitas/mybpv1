<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use MasterTag\DataHora;

/**
 * App\Models\RequisicaoVagaMovimentacao
 *
 * @property int $id
 * @property int $empresa_id
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
 * @property string|null $observacao
 * @property int $user_id
 * @property int|null $user_aprovacao_id
 * @property \Illuminate\Support\Carbon|null $data_aprovacao
 * @property string|null $obs_aprovacao
 * @property string|null $status_aprovacao
 * @property int|null $aprovacao_extra_id
 * @property \Illuminate\Support\Carbon|null $data_aprovacao_extra
 * @property string|null $obs_aprovacao_extra
 * @property string|null $status_aprovacao_extra
 * @property int|null $rh_aprovacao_id
 * @property \Illuminate\Support\Carbon|null $data_aprovacao_rh
 * @property string|null $obs_rh
 * @property string|null $status_aprovacao_rh
 * @property bool $aprovado_via_script
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVagaMovimentacao newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVagaMovimentacao newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RequisicaoVagaMovimentacao query()
 * @property string|null $posicao
 * @property string|null $processo
 * @property string|null $nome_indicacao
 * @property string|null $contrato
 * @property string|null $local_trabalho
 * @property string|null $horario
 * @property int|null $gestor_id
 * @property string|null $gestor
 * @property bool|null $ppra
 * @property string|null $salario
 * @property float|null $salario_valor
 * @property string|null $beneficio
 * @property string|null $beneficio_excecao
 * @property string|null $treinamento
 * @property string|null $treinamento_excecao
 * @property array<array-key, mixed>|null $custom_values
 * @property-read \App\Models\User|null $AprovacaoExtra
 * @property-read \App\Models\User|null $AprovacaoRh
 * @property-read \App\Models\AreaEtiqueta|null $Area
 * @property-read \App\Models\Vaga|null $Cargo
 * @property-read \App\Models\CentroCusto|null $CentroCusto
 * @property-read \App\Models\Cliente|null $Cliente
 * @property-read \App\Models\Cliente|null $Empresa
 * @property-read \App\Models\User|null $GestorContratacao
 * @property-read \App\Models\User|null $User
 * @property-read \App\Models\User|null $UserAprovacao
 * @property-read \App\Models\User|null $UserCadastrou
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read mixed $created_at_formatado
 * @property-read mixed $data_solicitacao
 * @property-read mixed $updated_at_formatado
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaMovimentacao onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaMovimentacao whereAprovacaoExtraId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaMovimentacao whereAprovadoViaScript($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaMovimentacao whereAreaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaMovimentacao whereBeneficio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaMovimentacao whereBeneficioExcecao($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaMovimentacao whereCargoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaMovimentacao whereCentroCustoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaMovimentacao whereClienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaMovimentacao whereContrato($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaMovimentacao whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaMovimentacao whereCustomValues($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaMovimentacao whereDataAprovacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaMovimentacao whereDataAprovacaoExtra($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaMovimentacao whereDataAprovacaoRh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaMovimentacao whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaMovimentacao whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaMovimentacao whereGestor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaMovimentacao whereGestorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaMovimentacao whereHorario($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaMovimentacao whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaMovimentacao whereImediata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaMovimentacao whereLocalTrabalho($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaMovimentacao whereNomeIndicacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaMovimentacao whereObsAprovacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaMovimentacao whereObsAprovacaoExtra($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaMovimentacao whereObsRh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaMovimentacao whereObservacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaMovimentacao wherePosicao($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaMovimentacao wherePpra($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaMovimentacao wherePrevisaoInicio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaMovimentacao wherePrioridade($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaMovimentacao whereProcesso($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaMovimentacao whereQuantidade($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaMovimentacao whereRhAprovacaoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaMovimentacao whereSalario($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaMovimentacao whereSalarioValor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaMovimentacao whereSolicitante($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaMovimentacao whereStatusAprovacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaMovimentacao whereStatusAprovacaoExtra($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaMovimentacao whereStatusAprovacaoRh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaMovimentacao whereTipoContratacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaMovimentacao whereTreinamento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaMovimentacao whereTreinamentoExcecao($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaMovimentacao whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaMovimentacao whereUserAprovacaoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaMovimentacao whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaMovimentacao withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequisicaoVagaMovimentacao withoutTrashed()
 * @mixin \Eloquent
 */
class RequisicaoVagaMovimentacao extends Model
{
    use HasFactory, TenantTrait, LogsActivity, HasActivitylogOptions, SoftDeletes;




    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }


    protected $table = 'requisicao_vagas_movimentacao';

    protected $fillable = [
        'empresa_id',
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
        'observacao',
        'user_id',
        'user_aprovacao_id',
        'data_aprovacao',
        'obs_aprovacao',
        'status_aprovacao',
        'aprovacao_extra_id',
        'data_aprovacao_extra',
        'obs_aprovacao_extra',
        'status_aprovacao_extra',
        'rh_aprovacao_id',
        'data_aprovacao_rh',
        'obs_rh',
        'status_aprovacao_rh',
        'aprovado_via_script',
        // Detalhes de contratação
        'posicao',
        'processo',
        'nome_indicacao',
        'contrato',
        'local_trabalho',
        'horario',
        'gestor_id',
        'gestor',
        'ppra',
        'salario',
        'salario_valor',
        'beneficio',
        'beneficio_excecao',
        'treinamento',
        'treinamento_excecao',
        'custom_values',
    ];

    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
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
        'observacao' => 'string',
        'user_id' => 'int',
        'user_aprovacao_id' => 'int',
        'data_aprovacao' => 'datetime:d/m/Y H:i:s',
        'obs_aprovacao' => 'string',
        'status_aprovacao' => 'string',
        'aprovacao_extra_id' => 'int',
        'data_aprovacao_extra' => 'datetime:d/m/Y H:i:s',
        'obs_aprovacao_extra' => 'string',
        'status_aprovacao_extra' => 'string',
        'rh_aprovacao_id' => 'int',
        'data_aprovacao_rh' => 'datetime:d/m/Y H:i:s',
        'obs_rh' => 'string',
        'status_aprovacao_rh' => 'string',
        'aprovado_via_script' => 'boolean',
        // Detalhes de contratação
        'posicao' => 'string',
        'processo' => 'string',
        'nome_indicacao' => 'string',
        'contrato' => 'string',
        'local_trabalho' => 'string',
        'horario' => 'string',
        'gestor_id' => 'int',
        'gestor' => 'string',
        'ppra' => 'boolean',
        'salario' => 'string',
        'salario_valor' => 'float',
        'beneficio' => 'string',
        'beneficio_excecao' => 'string',
        'treinamento' => 'string',
        'treinamento_excecao' => 'string',
        'custom_values' => 'array',
    ];

    protected $appends = ['data_solicitacao', 'created_at_formatado', 'updated_at_formatado'];

    public function getCustomValuesAttribute($value)
    {
        $decoded = is_string($value) ? json_decode($value, true) : $value;
        return is_array($decoded) ? $decoded : [];
    }

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

    public function setDataAprovacaoExtraAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($value);
            $this->attributes['data_aprovacao_extra'] = $data->dataHoraInsert();
        } else {
            $this->attributes['data_aprovacao_extra'] = null;
        }
    }

    public function setDataAprovacaoRhAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($value);
            $this->attributes['data_aprovacao_rh'] = $data->dataHoraInsert();
        } else {
            $this->attributes['data_aprovacao_rh'] = null;
        }
    }

    // Relacionamentos

    public function Empresa()
    {
        return $this->hasOne(Cliente::class, 'id', 'empresa_id');
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

    public function UserCadastrou()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
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

    public function GestorContratacao()
    {
        return $this->hasOne(User::class, 'id', 'gestor_id');
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
}
