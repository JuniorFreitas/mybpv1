<?php

namespace App\Models;

use App\Scopes\ScopeClientesEmpresa;
use App\Tenant\Traits\TenantTrait;
use App\Models\User;
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
 * @property string|null $tipo_aviso
 * @property int|null $user_aprovacao_id
 * @property mixed|null $data_aprovacao
 * @property string|null $obs_aprovacao
 * @property string|null $status_aprovacao
 * @property int|null $empresa_id
 * @property int|null $gestor_id
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereDataAprovacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereGestorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereObsAprovacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereStatusAprovacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereTipoAviso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereUserAprovacaoId($value)
 * @property-read User|null $GestorAprovacao
 * @property-read User|null $UserAprovacao
 * @property int|null $user_rh_id
 * @property string|null $resposta_rh
 * @property string|null $obs_rh
 * @property string|null $data_aprovacao_rh
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereDataAprovacaoRh($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereObsRh($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereRespostaRh($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereUserRhId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $Anexos
 * @property-read int|null $anexos_count
 */
class DemissaoPrevista extends Model
{
    use HasFactory, TenantTrait;

    protected $fillable = [
        'cliente_id',
        'colaborador_id',
        'centro_custo_id',
        'aviso',
        'data_demissao',
        'tipo_aviso',
        'valor',
        'user_id',
        'solicitante',
        'status',
        'obs',
        'user_aprovacao_id',
        'obs_aprovacao',
        'data_aprovacao',
        'status_aprovacao',
        'gestor_id',
        'empresa_id',
    ];

    protected $casts = [
        'id' => 'int',
        'cliente_id' => 'int',
        'colaborador_id' => 'int',
        'centro_custo_id' => 'int',
        'aviso' => 'string',
        'data_demissao' => 'date:d/m/Y',
        'valor' => 'float',
        'user_id' => 'int',
        'solicitante' => 'string',
        'status' => 'string',
        'obs' => 'string',
        'user_aprovacao_id' => 'int',
        'obs_aprovacao' => 'string',
        'data_aprovacao' => 'date:d/m/Y',
        'status_aprovacao' => 'string',
        'gestor_id' => 'int',
        'empresa_id' => 'int',
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
        return $this->belongsToMany(Arquivo::class, 'demissao_previstas_anexos', 'demissao_prevista_id', 'arquivo_id');
    }

}
