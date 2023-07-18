<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use MasterTag\DataHora;
/**
 * App\Models\MudancaCargo
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $Anexos
 * @property-read int|null $anexos_count
 * @property-read \App\Models\CentroCusto|null $CentroCustoAnterior
 * @property-read \App\Models\CentroCustoFilial|null $CentroCustoFilialAnterior
 * @property-read \App\Models\CentroCustoFilial|null $CentroCustoFilialNovo
 * @property-read \App\Models\CentroCusto|null $CentroCustoNovo
 * @property-read \App\Models\User|null $Colaborador
 * @property-read \App\Models\Cliente|null $Empresa
 * @property-read \App\Models\User|null $Gestor
 * @property-read \App\Models\User|null $GestorAprovacao
 * @property-read \App\Models\User|null $QuemDeletou
 * @property-read \App\Models\User|null $RhAprovacao
 * @property-read \App\Models\User|null $Solicitante
 * @property-read \App\Models\VagasAbertas|null $VagaAbertaAnterior
 * @property-read \App\Models\VagasAbertas|null $VagaAbertaNova
 * @property mixed $anterior_salario
 * @property mixed $data_aprovacao_gestor
 * @property mixed $data_aprovacao_rh
 * @property mixed $data_solicitacao
 * @property mixed $novo_salario
 * @method static \Illuminate\Database\Eloquent\Builder|MudancaCargo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MudancaCargo newQuery()
 * @method static \Illuminate\Database\Query\Builder|MudancaCargo onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|MudancaCargo query()
 * @method static \Illuminate\Database\Query\Builder|MudancaCargo withTrashed()
 * @method static \Illuminate\Database\Query\Builder|MudancaCargo withoutTrashed()
 * @mixin \Eloquent
 */
class MudancaCargo extends Model
{
    use HasFactory, TenantTrait, SoftDeletes;

    protected $table = "mudanca_cargo";

    protected $fillable = [
        'empresa_id',
        'admissao_id',
        'colaborador_id',
        'mantem_centro_custo',
        'anterior_centro_custo_id',
        'anterior_filial',
        'anterior_centro_custo_filial_id',
        'novo_centro_custo_id',
        'novo_filial',
        'novo_centro_custo_filial_id',
        'mantem_cargo',
        'anterior_vaga_aberta_id',
        'nova_vaga_aberta_id',
        'mantem_funcao',
        'anterior_funcao',
        'nova_funcao',
        'mantem_salario',
        'anterior_salario',
        'novo_salario',
        'solicitante_id',
        'obs_solicitante',
        'data_solicitacao',
        'gestor_id',
        'gestor_aprovacao_id',
        'obs_gestor_aprovacao',
        'status_aprovacao_gestor',
        'data_aprovacao_gestor',
        'rh_aprovacao_id',
        'obs_rh',
        'status_aprovacao_rh',
        'data_aprovacao_rh',
        'aprovado_via_script',
        'quem_deletou_id'
    ];

    protected $casts = [
        'empresa_id' => 'int',
        'admissao_id' => 'int',
        'colaborador_id' => 'int',
        'anterior_filial' => 'boolean',
        'anterior_centro_custo_id' => 'int',
        'mantem_centro_custo' => 'boolean',
        'anterior_centro_custo_filial_id' => 'int',
        'novo_centro_custo_id' => 'int',
        'novo_filial' => 'boolean',
        'novo_centro_custo_filial_id' => 'int',
        'mantem_cargo' => 'boolean',
        'anterior_vaga_aberta_id' => 'int',
        'nova_vaga_aberta_id' => 'int',
        'mantem_funcao' => 'boolean',
        'anterior_funcao' => 'string',
        'nova_funcao' => 'string',
        'mantem_salario' => 'boolean',
        'anterior_salario' => 'float',
        'novo_salario' => 'float',
        'solicitante_id' => 'int',
        'obs_solicitante' => 'string',
        'data_solicitacao' => 'string',
        'gestor_id' => 'int',
        'gestor_aprovacao_id' => 'int',
        'obs_gestor_aprovacao' => 'string',
        'status_aprovacao_gestor' => 'string',
        'data_aprovacao_gestor' => 'string',
        'rh_aprovacao_id' => 'int',
        'obs_rh' => 'string',
        'status_aprovacao_rh' => 'string',
        'data_aprovacao_rh' => 'string',
        'aprovado_via_script' => 'boolean',
        'quem_deletou_id' => 'int',
        'created_at' => 'datetime:d/m/Y à\s H:i:s',
        'updated_at' => 'datetime:d/m/Y à\s H:i:s'
    ];

    const STATUS_APROVADO = 'aprovado';
    const STATUS_REPROVADO = 'reprovado';

    const LISTA_STATUS_APROVACAO = [
        self::STATUS_APROVADO,
        self::STATUS_REPROVADO
    ];

    public function getAnteriorSalarioAttribute()
    {
        return number_format($this->attributes['anterior_salario'], 2, ',', '.');
    }

    public function setAnteriorSalarioAttribute($value)
    {
        $this->attributes['anterior_salario'] = Sistema::DinheiroInsert($value);
    }

    public function getNovoSalarioAttribute()
    {
        return number_format($this->attributes['novo_salario'], 2, ',', '.');
    }

    public function setNovoSalarioAttribute($value)
    {
        $this->attributes['novo_salario'] = Sistema::DinheiroInsert($value);
    }

    public function setDataSolicitacaoAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($value);
            $this->attributes['data_solicitacao'] = $data->dataInsert();
        } else {
            $this->attributes['data_solicitacao'] = null;
        }
    }

    public function getDataSolicitacaoAttribute($value)
    {
        $data = new DataHora($this->attributes['data_solicitacao']);
        return $data->dataCompleta();
    }

    public function setDataAprovacaoGestorAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($value);
            $this->attributes['data_aprovacao_gestor'] = $data->dataInsert();
        } else {
            $this->attributes['data_aprovacao_gestor'] = null;
        }
    }

    public function getDataAprovacaoGestorAttribute($value)
    {
        $data = new DataHora($this->attributes['data_aprovacao_gestor']);
        return $data->dataCompleta();
    }

    public function setDataAprovacaoRhAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($value);
            $this->attributes['data_aprovacao_rh'] = $data->dataInsert();
        } else {
            $this->attributes['data_aprovacao_rh'] = null;
        }
    }

    public function getDataAprovacaoRhAttribute($value)
    {
        $data = new DataHora($this->attributes['data_aprovacao_rh']);
        return $data->dataCompleta();
    }

    public function Admissao()
    {
        return $this->hasOne(Admissao::class, 'id', 'admissao_id');
    }

    public function Empresa()
    {
        return $this->hasOne(Cliente::class, 'id', 'cliente_id');
    }

    public function Colaborador()
    {
        return $this->hasOne(User::class, 'id', 'colaborador_id');
    }

    public function CentroCustoNovo()
    {
        return $this->hasOne(CentroCusto::class, 'id', 'novo_centro_custo_id');
    }

    public function CentroCustoAnterior()
    {
        return $this->hasOne(CentroCusto::class, 'id', 'anterior_centro_custo_id');
    }

    public function CentroCustoFilialAnterior()
    {
        return $this->hasOne(CentroCustoFilial::class, 'id', 'anterior_centro_custo_filial_id');
    }

    public function CentroCustoFilialNovo()
    {
        return $this->hasOne(CentroCustoFilial::class, 'id', 'novo_centro_custo_filial_id');
    }

    public function VagaAbertaAnterior()
    {
        return $this->hasOne(VagasAbertas::class, 'id', 'anterior_vaga_aberta_id');
    }

    public function VagaAbertaNova()
    {
        return $this->hasOne(VagasAbertas::class, 'id', 'nova_vaga_aberta_id');
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

    public function QuemDeletou()
    {
        return $this->hasOne(User::class, 'id', 'quem_deletou_id');
    }

    public function Anexos()
    {
        return $this->belongsToMany(Arquivo::class, 'mudanca_cargo_anexos', 'mudanca_cargo_id', 'arquivo_id');
    }
}
