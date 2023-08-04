<?php

namespace App\Models;

use App\Scopes\ScopeClientesEmpresa;
use App\Tenant\Traits\TenantTrait;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;

/**
 * App\Models\MudaCargoPrevista
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
 * @property string|null $autorizado_por
 * @property string|null $obs
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
 * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista query()
 * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereAutorizadoPor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereCargoAnteriorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereCentroCustoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereClienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereColaboradorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereDataAprovacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereGestorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereNovoCargoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereNovoSalario($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereObs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereObsAprovacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereSalarioAnterior($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereStatusAprovacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereUserAprovacaoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereUserId($value)
 * @mixin \Eloquent
 */
class MudaCargoPrevista extends Model
{
    use HasFactory, TenantTrait;

    protected $fillable = [
        'cliente_id',
        'colaborador_id',
        'centro_custo_id',
        'cargo_anterior_id',
        'salario_anterior',
        'novo_cargo_id',
        'novo_salario',
        'user_id',
        'obs',
        'user_aprovacao_id',
        'data_aprovacao',
        'obs_aprovacao',
        'status_aprovacao',
        'empresa_id',
        'gestor_id',
    ];

    protected $casts = [
        'id' => 'int',
        'cliente_id' => 'int',
        'colaborador_id' => 'int',
        'centro_custo_id' => 'int',
        'cargo_anterior_id' => 'int',
        'salario_anterior' => 'float',
        'novo_cargo_id' => 'int',
        'novo_salario' => 'float',
        'user_id' => 'int',
        'obs' => 'string',
        'created_at' => 'datetime:d/m/Y à\s H:i:s',
        'updated_at' => 'datetime:d/m/Y à\s H:i:s',
        'user_aprovacao_id' => 'int',
        'data_aprovacao' => 'date:d/m/Y',
        'obs_aprovacao' => 'string',
        'status_aprovacao' => 'string',
        'empresa_id' => 'int',
        'gestor_id' => 'int'
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

    public function GestorAprovacao()
    {
        return $this->hasOne(User::class, 'id', 'gestor_id');
    }

    public function UserAprovacao()
    {
        return $this->hasOne(User::class, 'id', 'user_aprovacao_id');
    }

    public function Anexos()
    {
        return $this->belongsToMany(Arquivo::class, 'muda_cargo_previstas_anexos', 'muda_cargo_prevista_id', 'arquivo_id');
    }
}
