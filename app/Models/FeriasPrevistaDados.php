<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use DateTimeInterface;

/**
 * App\Models\FeriasPrevistaDados
 *
 * @property int $id
 * @property int $ferias_prevista_id referencia ao colaborador HASONE
 * @property int|null $centro_custo_id
 * @property int|null $solicitante_id
 * @property mixed|null $data_saida
 * @property int|null $qnt_dias
 * @property mixed|null $data_retorno
 * @property int|null $dias_saldo
 * @property string|null $status
 * @property string|null $obs
 * @property string|null $periodo_aquisitivo
 * @property mixed|null $ultima_data
 * @property \datetime|null $created_at
 * @property \datetime|null $updated_at
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
 * @property-read \App\Models\CentroCusto|null $CentroCusto
 * @property-read \App\Models\FeriasPrevistaMov|null $FeriasPrevistaMov
 * @property-read \App\Models\User|null $GestorAprovacao
 * @property-read \App\Models\User|null $QuemAprovou
 * @property-read \App\Models\User|null $RhAprovacao
 * @property-read \App\Models\User|null $UserCadastrou
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados query()
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados whereCentroCustoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados whereDataAprovacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados whereDataAprovacaoRh($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados whereDataRetorno($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados whereDataSaida($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados whereDiasSaldo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados whereFeriasPrevistaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados whereObs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados whereObsAprovacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados whereObsRh($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados wherePeriodoAquisitivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados whereQntDias($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados whereQntFaltas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados whereRespostaRh($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados whereSolicitanteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados whereStatusAprovacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados whereTemFaltas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados whereUserAprovacaoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados whereUserRhId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeriasPrevistaDados whereUltimaData($value)
 * @mixin \Eloquent
 */
class FeriasPrevistaDados extends Model
{
    use HasFactory;


    protected $fillable = [
        'ferias_prevista_id',
        'centro_custo_id',
        'data_saida',
        'qnt_dias',
        'data_retorno',
        'dias_saldo',
        'solicitante_id',
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
        'periodo_aquisitivo_id',
        'ultima_data',
    ];

    protected $casts = [
        'id' => 'int',
        'ferias_prevista_id' => 'int',
        'centro_custo_id' => 'int',
        'data_saida' => 'date:d/m/Y',
        'qnt_dias' => 'int',
        'data_retorno' => 'date:d/m/Y',
        'dias_saldo' => 'int',
        'solicitante_id' => 'int',
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

        'periodo_aquisitivo_id' => 'int',
        'ultima_data' => 'string',
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

    //Acessor ->data_inicio
    public function getCreatedAtAttribute($value)
    {
        $data = new DataHora($this->attributes['created_at']);
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

    public function getUltimaDataAttribute($value)
    {
        if ($value) {
            $data = new DataHora($this->attributes['ultima_data']);
            return $data->dataCompleta();
        }
    }

    //Modificador ->data_fim
    public function setUltimaDataAttribute($value)
    {
        if ($value) {
            $data = new DataHora($value);
            $this->attributes['ultima_data'] = $data->dataInsert();
        }
    }

    public function CentroCusto()
    {
        return $this->hasOne(CentroCusto::class, 'id', 'centro_custo_id');
    }

    public function UserCadastrou()
    {
        return $this->hasOne(User::class, 'id', 'solicitante_id');
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

    public function FeriasPrevistaMov()
    {
        return $this->hasOne(FeriasPrevistaMov::class, 'id', 'ferias_prevista_id');
    }

    public function PeriodoAquisitivo()
    {
        return $this->hasOne(PeriodoAquisitivo::class, 'id', 'periodo_aquisitivo_id');
    }
}
