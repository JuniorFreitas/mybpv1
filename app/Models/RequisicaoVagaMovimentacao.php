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
