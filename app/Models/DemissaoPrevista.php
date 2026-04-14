<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use App\Tenant\Traits\TenantTrait;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;

/**
 * App\Models\DemissaoPrevista
 *
 * @property int $id
 * @property int|null $cliente_id
 * @property int $colaborador_id
 * @property int $centro_custo_id
 * @property string|null $aviso
 * @property \Illuminate\Support\Carbon $data_demissao
 * @property string|null $tipo_aviso
 * @property float $valor
 * @property int|null $user_id
 * @property string|null $solicitante
 * @property string|null $status
 * @property string|null $obs
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $user_aprovacao_id
 * @property \Illuminate\Support\Carbon|null $data_aprovacao
 * @property string|null $obs_aprovacao
 * @property string|null $status_aprovacao
 * @property int|null $empresa_id
 * @property int|null $gestor_id
 * @property int|null $filial
 * @property int|null $centro_custo_filial_id
 * @property int|null $rh_aprovacao_id
 * @property string|null $obs_rh
 * @property string|null $status_aprovacao_rh
 * @property \Illuminate\Support\Carbon|null $data_aprovacao_rh
 * @property bool $aprovado_via_script
 * @property int|null $quem_deletou_id
 * @property string|null $deleted_at
 * @property int|null $aprovacao_extra_id
 * @property string|null $status_aprovacao_extra
 * @property string|null $obs_aprovacao_extra
 * @property \Illuminate\Support\Carbon|null $data_aprovacao_extra
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $Anexos
 * @property-read int|null $anexos_count
 * @property-read \App\Models\CentroCusto|null $CentroCusto
 * @property-read \App\Models\CentroCustoFilial|null $CentroCustoFilial
 * @property-read \App\Models\Cliente|null $Cliente
 * @property-read User|null $Colaborador
 * @property-read User|null $GestorAprovacao
 * @property-read User|null $RhAprovacao
 * @property-read User|null $UserAprovacao
 * @property-read User|null $AprovacaoExtra
 * @property-read User|null $UserCadastrou
 * @property-read mixed $valor_format
 * @property-write mixed $data_pagamento
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista query()
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereAprovadoViaScript($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereAviso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereCentroCustoFilialId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereCentroCustoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereClienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereColaboradorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereDataAprovacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereDataAprovacaoRh($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereDataDemissao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereFilial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereGestorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereObs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereObsAprovacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereObsRh($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereQuemDeletouId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereRhAprovacaoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereSolicitante($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereStatusAprovacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereStatusAprovacaoRh($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereTipoAviso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereUserAprovacaoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DemissaoPrevista whereValor($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DemissaoPrevista whereAprovacaoExtraId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DemissaoPrevista whereDataAprovacaoExtra($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DemissaoPrevista whereObsAprovacaoExtra($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DemissaoPrevista whereStatusAprovacaoExtra($value)
 * @mixin \Eloquent
 */
class DemissaoPrevista extends Model
{
    use LogsActivity, HasActivitylogOptions, TenantTrait;

    protected static $logName = 'DemissaoPrevista';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

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
        'filial',
        'centro_custo_filial_id',
        'empresa_id',
        'rh_aprovacao_id',
        'obs_rh',
        'status_aprovacao_rh',
        'data_aprovacao_rh',
        'aprovado_via_script',
        'quem_deletou_id',
        'aprovacao_extra_id',
        'status_aprovacao_extra',
        'obs_aprovacao_extra',
        'data_aprovacao_extra'
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
        'filial' => 'boolean',
        'centro_custo_filial_id' => 'int',
        'empresa_id' => 'int',
        'created_at' => 'datetime:d/m/Y à\s H:i:s',
        'updated_at' => 'datetime:d/m/Y à\s H:i:s',
        'rh_aprovacao_id' => 'int',
        'obs_rh' => 'string',
        'status_aprovacao_rh' => 'string',
        'data_aprovacao_rh' => 'datetime:d/m/Y à\s H:i:s',
        'aprovado_via_script' => 'boolean',
        'quem_deletou_id' => 'int',
        'aprovacao_extra_id' => 'int',
        'status_aprovacao_extra' => 'string',
        'obs_aprovacao_extra' => 'string',
        'data_aprovacao_extra' => 'datetime:d/m/Y à\s H:i:s'
    ];

    const STATUS_APROVADO = 'aprovado';
    const STATUS_REPROVADO = 'reprovado';

    protected function serializeDate(DateTimeInterface $date)
    {
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

    public function CentroCustoFilial()
    {
        return $this->hasOne(CentroCustoFilial::class, 'id', 'centro_custo_filial_id');
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

    public function RhAprovacao()
    {
        return $this->hasOne(User::class, 'id', 'rh_aprovacao_id');
    }

    public function AprovacaoExtra()
    {
        return $this->hasOne(User::class, 'id', 'aprovacao_extra_id');
    }

    public function Anexos()
    {
        return $this->belongsToMany(Arquivo::class, 'demissao_previstas_anexos', 'demissao_prevista_id', 'arquivo_id');
    }
}
