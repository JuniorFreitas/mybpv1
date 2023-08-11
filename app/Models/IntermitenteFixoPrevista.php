<?php

namespace App\Models;

use App\Scopes\ScopeClientesEmpresa;
use App\Tenant\Traits\TenantTrait;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\IntermitenteFixoPrevista
 *
 * @property int $id
 * @property int|null $cliente_id
 * @property int $colaborador_id
 * @property int $centro_custo_id
 * @property int|null $cargo_anterior_id
 * @property float|null $salario_anterior
 * @property int|null $novo_cargo_id
 * @property float|null $novo_salario
 * @property int|null $user_id
 * @property string|null $data_modificacao
 * @property string|null $autorizado_por
 * @property string|null $motivos
 * @property \datetime|null $created_at
 * @property \datetime|null $updated_at
 * @property int|null $user_aprovacao_id
 * @property mixed|null $data_aprovacao
 * @property string|null $obs_aprovacao
 * @property string|null $status_aprovacao
 * @property int|null $empresa_id
 * @property int|null $gestor_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $Anexos
 * @property-read int|null $anexos_count
 * @property-read \App\Models\Vaga|null $CargoAnterior
 * @property-read \App\Models\CentroCusto|null $CentroCusto
 * @property-read \App\Models\Cliente|null $Cliente
 * @property-read \App\Models\User|null $Colaborador
 * @property-read \App\Models\User|null $GestorAprovacao
 * @property-read \App\Models\Vaga|null $NovoCargo
 * @property-read \App\Models\User|null $UserAprovacao
 * @property-read \App\Models\User|null $UserCadastrou
 * @property-read mixed $novo_salario_format
 * @property-read mixed $salario_anterior_format
 * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista query()
 * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereAutorizadoPor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereCargoAnteriorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereCentroCustoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereClienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereColaboradorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereDataAprovacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereDataModificacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereGestorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereMotivos($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereNovoCargoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereNovoSalario($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereObsAprovacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereSalarioAnterior($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereStatusAprovacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereUserAprovacaoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IntermitenteFixoPrevista whereUserId($value)
 * @mixin \Eloquent
 */
class IntermitenteFixoPrevista extends Model
{
    use HasFactory, TenantTrait, SoftDeletes;

    protected $fillable = [
        'cliente_id',
        'colaborador_id',
        'centro_custo_id',
        'filial',
        'centro_custo_filial_id',
        'cargo_anterior_id',
        'salario_anterior',
        'novo_cargo_id',
        'novo_salario',
        'user_id',
        'motivos',
        'user_aprovacao_id',
        'data_aprovacao',
        'obs_aprovacao',
        'status_aprovacao',
        'empresa_id',
        'gestor_id',
        'anterior_vaga_aberta_id',
        'nova_vaga_aberta_id',
        'area_etiqueta_id',
        'rh_aprovacao_id',
        'obs_rh',
        'status_aprovacao_rh',
        'data_aprovacao_rh',
        'aprovado_via_script',
        'quem_deletou_id',
    ];

    protected $casts = [
        'id' => 'int',
        'cliente_id' => 'int',
        'colaborador_id' => 'int',
        'centro_custo_id' => 'int',
        'filial' => 'boolean',
        'centro_custo_filial_id' => 'int',
        'cargo_anterior_id' => 'int',
        'salario_anterior' => 'float',
        'novo_cargo_id' => 'int',
        'novo_salario' => 'float',
        'user_id' => 'int',
        'motivos' => 'string',
        'created_at' => 'datetime:d/m/Y à\s H:i:s',
        'updated_at' => 'datetime:d/m/Y à\s H:i:s',
        'user_aprovacao_id' => 'int',
        'data_aprovacao' => 'date:d/m/Y',
        'obs_aprovacao' => 'string',
        'status_aprovacao' => 'string',
        'empresa_id' => 'int',
        'gestor_id'=>'int',
        'anterior_vaga_aberta_id' => 'int',
        'nova_vaga_aberta_id' => 'int',
        'area_etiqueta_id' => 'int',
        'rh_aprovacao_id' => 'int',
        'obs_rh' => 'string',
        'status_aprovacao_rh' => 'string',
        'data_aprovacao_rh' => 'date:d/m/Y',
        'aprovado_via_script' => 'boolean',
        'quem_deletou_id' => 'int',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    protected $appends = ['salario_anterior_format', 'novo_salario_format'];


    public function getSalarioAnteriorFormatAttribute()
    {
        return number_format($this->attributes['salario_anterior'], 2, ',', '.');
    }

    public function setSalarioAnteriorAttribute($value)
    {
        $this->attributes['salario_anterior'] = Sistema::DinheiroInsert($value);
    }

    public function getNovoSalarioFormatAttribute()
    {
        return number_format($this->attributes['novo_salario'], 2, ',', '.');
    }

    public function setNovoSalarioAttribute($value)
    {
        $this->attributes['novo_salario'] = Sistema::DinheiroInsert($value);
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

    public function UserCadastrou()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function CargoAnterior()
    {
        return $this->hasOne(Vaga::class, 'id', 'cargo_anterior_id');
    }

    public function NovoCargo()
    {
        return $this->hasOne(Vaga::class, 'id', 'novo_cargo_id');
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

    public function QuemDeletou()
    {
        return $this->hasOne(User::class, 'id', 'quem_deletou_id');
    }

    public function Anexos()
    {
        return $this->belongsToMany(Arquivo::class, 'intermitente_fixo_previstas_anexos', 'intermitente_fixo_prevista_id', 'arquivo_id');
    }

}
