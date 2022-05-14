<?php

namespace App\Models;

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
 * @property int $cliente_id
 * @property int|null $colaborador_id
 * @property int $centro_custo_id
 * @property string $tipo
 * @property string|null $periodo_dias
 * @property int|null $user_id
 * @property string|null $solicitante
 * @property string|null $obs
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista query()
 * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereCentroCustoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereClienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereColaboradorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereObs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista wherePeriodoDias($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereSolicitante($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereUserId($value)
 * @mixin \Eloquent
 * @property-read \App\Models\CentroCusto|null $CentroCusto
 * @property-read \App\Models\Cliente|null $Cliente
 * @property-read \App\Models\User|null $Colaborador
 * @property-read \App\Models\User|null $UserCadastrou
 * @property int|null $user_aprovacao_id
 * @property mixed|null $data_aprovacao
 * @property string|null $obs_aprovacao
 * @property string|null $status_aprovacao
 * @property int|null $empresa_id
 * @property int|null $gestor_id
 * @property-read \App\Models\User|null $GestorAprovacao
 * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereDataAprovacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereGestorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereObsAprovacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereStatusAprovacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ValorExtraPrevista whereUserAprovacaoId($value)
 */
class ValorExtraPrevista extends Model
{
    use HasFactory, TenantTrait;

    protected $fillable = [
        'colaborador_id',
        'centro_custo_id',
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
    ];

    protected $casts = [
        'id' => 'int',
        'cliente_id' => 'int',
        'colaborador_id' => 'int',
        'centro_custo_id' => 'int',
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
        'gestor_id' => 'int'
    ];

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

}
