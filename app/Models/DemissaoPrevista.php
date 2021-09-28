<?php

namespace App\Models;

use App\Scopes\ScopeClientesEmpresa;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;

/**
 * App\Models\DemissaoPrevista
 *
 * @property int $id
 * @property int $cliente_id
 * @property int $colaborador_id
 * @property int $centro_custo_id
 * @property string|null $aviso
 * @property mixed $data_demissao
 * @property mixed $data_pagamento
 * @property float $valor
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
 * @property-read mixed $valor_format
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista query()
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereAviso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereCentroCustoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereClienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereColaboradorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereDataDemissao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereDataPagamento($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereObs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereSolicitante($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereValor($value)
 * @mixin \Eloquent
 * @property-read \App\Models\User|null $Colaborador
 */
class DemissaoPrevista extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'colaborador_id',
        'centro_custo_id',
        'aviso',
        'data_demissao',
        'data_pagamento',
        'valor',
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
        'aviso' => 'string',
        'data_demissao' => 'date:d/m/Y',
        'data_pagamento' => 'date:d/m/Y',
        'valor' => 'float',
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

    protected $appends = ['valor_format'];

    public function setDataDemissaoAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($value);
            $this->attributes['data_demissao'] = $data->dataInsert();
        } else {
            $this->attributes['data_demissao'] = null;
        }
    }

    public function setDataPagamentoAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($value);
            $this->attributes['data_pagamento'] = $data->dataInsert();
        } else {
            $this->attributes['data_pagamento'] = null;
        }
    }

    public function getValorFormatAttribute()
    {
        return number_format($this->attributes['valor'], 2, ',', '.');
    }


    public function setValorAttribute($value)
    {
        $this->attributes['valor'] = Sistema::DinheiroInsert($value);
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
