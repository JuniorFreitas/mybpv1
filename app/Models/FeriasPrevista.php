<?php

namespace App\Models;

use App\Scopes\ScopeClientesEmpresa;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;

/**
 * App\Models\FeriasPrevista
 *
 * @property int $id
 * @property int $cliente_id
 * @property int $colaborador_id
 * @property int $centro_custo_id
 * @property mixed $data_saida
 * @property int $qnt_dias
 * @property mixed $data_retorno
 * @property int $dias_saldo
 * @property int|null $user_id
 * @property string|null $solicitante
 * @property string|null $status
 * @property string|null $obs
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CentroCusto|null $CentroCusto
 * @property-read \App\Models\Cliente|null $Cliente
 * @property-read \App\Models\User|null $Funcionario
 * @property-read \App\Models\User|null $UserCadastrou
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista query()
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereCentroCustoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereClienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereColaboradorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereDataRetorno($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereDataSaida($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereDiasSaldo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereObs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereQntDias($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereSolicitante($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereUserId($value)
 * @mixin \Eloquent
 * @property-read \App\Models\User|null $Colaborador
 */
class FeriasPrevista extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'colaborador_id',
        'centro_custo_id',
        'data_saida',
        'qnt_dias',
        'data_retorno',
        'dias_saldo',
        'user_id',
        'solicitante',
        'status',
        'obs',
    ];

    protected $casts = [
        'id' => 'int',
        'cliente_id' => 'int',
        'colaborador_id' => 'int',
        'centro_custo_id' => 'int',
        'data_saida' => 'date:d/m/Y',
        'qnt_dias' => 'int',
        'data_retorno' => 'date:d/m/Y',
        'dias_saldo' => 'int',
        'user_id' => 'int',
        'solicitante' => 'string',
        'status' => 'string',
        'obs' => 'string',
        'created_at' => 'datetime:d/m/Y à\s H:i:s',
        'updated_at' => 'datetime:d/m/Y à\s H:i:s',
    ];

    protected function serializeDate(DateTimeInterface $date) {
        return $date->format('Y-m-d H:i:s');
    }


    public function setDataSaidaAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($value);
            $this->attributes['data_saida'] = $data->dataInsert();
        } else {
            $this->attributes['data_saida'] = null;
        }
    }

    public function setDataRetornoAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($value);
            $this->attributes['data_retorno'] = $data->dataInsert();
        } else {
            $this->attributes['data_retorno'] = null;
        }
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
