<?php

namespace App\Models;

use App\Scopes\ScopeClientesEmpresa;
use App\Tenant\Traits\TenantTrait;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;

/**
 * App\Models\AdmissoesPrevista
 *
 * @property int $id
 * @property int $cliente_id
 * @property int|null $colaborador_id
 * @property int $centro_custo_id
 * @property string $tipo_contrato
 * @property int $cargo_id
 * @property mixed $data_admissao
 * @property float $salario
 * @property int|null $user_id
 * @property string|null $solicitante
 * @property string|null $obs
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CentroCusto|null $CentroCusto
 * @property-read \App\Models\Cliente|null $Cliente
 * @property-read \App\Models\User|null $Funcionario
 * @property-read \App\Models\User|null $UserCadastrou
 * @property-read mixed $salario_format
 * @property-write mixed $valor
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista query()
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereCargoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereCentroCustoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereClienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereColaboradorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereDataAdmissao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereObs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereSalario($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereSolicitante($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereTipoContrato($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdmissoesPrevista whereUserId($value)
 * @mixin \Eloquent
 * @property-read \App\Models\Vaga|null $Cargo
 * @property-read \App\Models\User|null $Colaborador
 */
class AdmissoesPrevista extends Model
{
    use HasFactory, TenantTrait;

    protected $fillable = [
        'colaborador_id',
        'centro_custo_id',
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
        'empresa_id',
        'gestor_id',
    ];

    protected $casts = [
        'id' => 'int',
        'cliente_id' => 'int',
        'colaborador_id' => 'int',
        'centro_custo_id' => 'int',
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
        'empresa_id' => 'int',
        'gestor_id'=>'int'
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    protected $appends = ['salario_format'];

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
}
