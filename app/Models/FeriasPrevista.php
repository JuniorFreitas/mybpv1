<?php

namespace App\Models;

use App\Scopes\ScopeClientesEmpresa;
use App\Models\User;
use App\Tenant\Traits\TenantTrait;
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
 * @property int|null $user_aprovacao_id
 * @property mixed|null $data_aprovacao
 * @property string|null $obs_aprovacao
 * @property string|null $status_aprovacao
 * @property bool $tem_faltas
 * @property int|null $qnt_faltas
 * @property int|null $user_rh_id
 * @property string|null $resposta_rh
 * @property string|null $obs_rh
 * @property mixed|null $data_aprovacao_rh
 * @property int|null $empresa_id
 * @property-read User|null $GestorAprovacao
 * @property-read User|null $QuemAprovou
 * @property-read User|null $RhAprovacao
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereDataAprovacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereDataAprovacaoRh($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereObsAprovacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereObsRh($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereQntFaltas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereRespostaRh($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereStatusAprovacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereTemFaltas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereUserAprovacaoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevista whereUserRhId($value)
 */
class FeriasPrevista extends Model
{
    use HasFactory, TenantTrait;

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

        'user_aprovacao_id',
        'obs_aprovacao',
        'data_aprovacao',
        'status_aprovacao',
        'gestor_id',
        'tem_faltas',
        'qnt_faltas',
        'user_rh_id',
        'resposta_rh',
        'obs_rh',
        'data_aprovacao_rh',
        'empresa_id',
        'periodo_aquisitivo',
        'ultima_data',
        'periodo_aquisitivo_id'
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

        'user_aprovacao_id' => 'int',
        'obs_aprovacao' => 'string',
        'data_aprovacao' => 'date:d/m/Y',
        'status_aprovacao' => 'string',
        'gestor_id' => 'int',
        'tem_faltas' => 'boolean',
        'qnt_faltas' => 'int',
        'user_rh_id' => 'int',
        'resposta_rh' => 'string',
        'obs_rh' => 'string',
        'data_aprovacao_rh' => 'date:d/m/Y',
        'created_at' => 'datetime:d/m/Y à\s H:i:s',
        'updated_at' => 'datetime:d/m/Y à\s H:i:s',

        'empresa_id' => 'int',

        'periodo_aquisitivo' => 'string',
        'ultima_data' => 'date:d/m/Y',

        'periodo_aquisitivo_id' => 'int',
    ];

    public function setDataSaidaAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($value);
            $this->attributes['data_saida'] = $data->dataInsert();
        } else {
            $this->attributes['data_saida'] = null;
        }
    }

    //Acessor ->data_inicio
    public function getDataSaidaAttribute($value)
    {
        $data = new DataHora($this->attributes['data_saida']);
        return $data->dataCompleta();
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

    //Acessor ->data_inicio
    public function getDataRetornoAttribute($value)
    {
        $data = new DataHora($this->attributes['data_retorno']);
        return $data->dataCompleta();
    }

    //Acessor ->data_inicio
    public function getDataAprovacaoAttribute($value)
    {
        $data = new DataHora($this->attributes['data_aprovacao']);
        return $data->dataCompleta();
    }

    public function Cliente()
    {
        return $this->hasOne(Cliente::class, 'id', 'cliente_id');
    }

    public function Colaborador()
    {
        return $this->hasOne(Curriculo::class, 'id', 'colaborador_id');
    }

    public function CentroCusto()
    {
        return $this->hasOne(CentroCusto::class, 'id', 'centro_custo_id');
    }

    public function UserCadastrou()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function QuemAprovou()
    {
        return $this->hasOne(User::class, 'id', 'user_aprovacao_id');
    }

    public function GestorAprovacao()
    {
        return $this->hasOne(User::class, 'id', 'gestor_id');
    }

    public function RhAprovacao()
    {
        return $this->hasOne(User::class, 'id', 'user_rh_id');
    }

    public function PeriodoAquisitivo()
    {
        return $this->hasOne(PeriodoAquisitivo::class, 'id', 'periodo_aquisitivo_id');
    }
}
