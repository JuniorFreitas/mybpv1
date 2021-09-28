<?php

namespace App\Models;

use App\Scopes\ScopeClientesEmpresa;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;

/**
 * App\Models\MudaCargoPrevista
 *
 * @property int $id
 * @property int $cliente_id
 * @property int $funcionario_id
 * @property int $centro_custo_id
 * @property string|null $tipo
 * @property int|null $cargo_anterior_id
 * @property string|null $salario_anterior
 * @property int|null $novo_cargo_id
 * @property string|null $novo_salario
 * @property int|null $user_id
 * @property string|null $autorizado_por
 * @property string|null $obs
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista query()
 * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereAutorizadoPor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereCargoAnteriorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereCentroCustoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereClienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereFuncionarioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereNovoCargoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereNovoSalario($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereObs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereSalarioAnterior($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereUserId($value)
 * @mixin \Eloquent
 * @property int $colaborador_id
 * @property-read \App\Models\Vaga|null $CargoAnterior
 * @property-read \App\Models\CentroCusto|null $CentroCusto
 * @property-read \App\Models\Cliente|null $Cliente
 * @property-read \App\Models\User|null $Colaborador
 * @property-read \App\Models\Vaga|null $NovoCargo
 * @property-read \App\Models\User|null $UserCadastrou
 * @property-read mixed $novo_salario_format
 * @property-read mixed $salario_anterior_format
 * @method static \Illuminate\Database\Eloquent\Builder|MudaCargoPrevista whereColaboradorId($value)
 */
class MudaCargoPrevista extends Model
{
    use HasFactory;

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

    //Scopo de ClienteID (Empresa)
    protected static function booted()
    {
        static::creating(function ($model) {
            $model->user_id = auth()->id();
        });

        static::updating(function ($model) {
            $model->user_id = auth()->id();
        });

        static::addGlobalScope(new ScopeClientesEmpresa);
    }
}
