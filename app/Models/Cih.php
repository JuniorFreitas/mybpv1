<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\Cih
 *
 * @property int $id
 * @property int|null $tag_id
 * @property int|null $gestor_id
 * @property string|null $outra_tag
 * @property bool $varios_colaboradores
 * @property string|null $colaboradores_avulso
 * @property int|null $feedback_id somente quem foi admitido
 * @property int|null $cliente_id Cliente empresa
 * @property int $user_lancamento_id Responsavel pelo lançamenro usuario em sessão
 * @property int|null $area_id
 * @property string|null $outra_area
 * @property string|null $obs_lancamento Responsavel pela aprovação usuario em sessão
 * @property string $data_lancamento
 * @property string $acao
 * @property int|null $user_aprovacao_id Responsavel pela aprovação usuario em sessão
 * @property string|null $obs_aprovacao Responsavel pela aprovação usuario em sessão
 * @property string|null $data_aprovacao
 * @property string|null $status aberto, aprovado
 * @property int|null $user_rh_id
 * @property string|null $resposta_rh
 * @property string|null $obs_rh
 * @property string|null $data_aprovacao_rh
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $empresa_id
 * @property int|null $centro_custo_id
 * @property string|null $centro_custo_outro
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $user_deletou_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $Anexos
 * @property-read int|null $anexos_count
 * @property-read \App\Models\AreaEtiqueta|null $Area
 * @property-read \App\Models\CentroCusto|null $CentroDeCusto
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FeedbackCurriculo> $CihFeedbacks
 * @property-read int|null $cih_feedbacks_count
 * @property-read \App\Models\Cliente|null $Cliente
 * @property-read \App\Models\FeedbackCurriculo|null $Colaborador
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FeedbackCurriculo> $Colaboradores
 * @property-read int|null $colaboradores_count
 * @property-read \App\Models\User|null $Empresa
 * @property-read \App\Models\User|null $GestorAprovacao
 * @property-read \App\Models\User|null $ResponsavelAprovacao
 * @property-read \App\Models\User|null $ResponsavelLancamento
 * @property-read \App\Models\User|null $RhAprovacao
 * @property-read \App\Models\CihTag|null $Tag
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static Builder|Cih newModelQuery()
 * @method static Builder|Cih newQuery()
 * @method static Builder|Cih onlyTrashed()
 * @method static Builder|Cih query()
 * @method static Builder|Cih vinculados()
 * @method static Builder|Cih whereAcao($value)
 * @method static Builder|Cih whereAreaId($value)
 * @method static Builder|Cih whereCentroCustoId($value)
 * @method static Builder|Cih whereCentroCustoOutro($value)
 * @method static Builder|Cih whereClienteId($value)
 * @method static Builder|Cih whereColaboradoresAvulso($value)
 * @method static Builder|Cih whereCreatedAt($value)
 * @method static Builder|Cih whereDataAprovacao($value)
 * @method static Builder|Cih whereDataAprovacaoRh($value)
 * @method static Builder|Cih whereDataLancamento($value)
 * @method static Builder|Cih whereDeletedAt($value)
 * @method static Builder|Cih whereEmpresaId($value)
 * @method static Builder|Cih whereFeedbackId($value)
 * @method static Builder|Cih whereGestorId($value)
 * @method static Builder|Cih whereId($value)
 * @method static Builder|Cih whereObsAprovacao($value)
 * @method static Builder|Cih whereObsLancamento($value)
 * @method static Builder|Cih whereObsRh($value)
 * @method static Builder|Cih whereOutraArea($value)
 * @method static Builder|Cih whereOutraTag($value)
 * @method static Builder|Cih whereRespostaRh($value)
 * @method static Builder|Cih whereStatus($value)
 * @method static Builder|Cih whereTagId($value)
 * @method static Builder|Cih whereUpdatedAt($value)
 * @method static Builder|Cih whereUserAprovacaoId($value)
 * @method static Builder|Cih whereUserDeletouId($value)
 * @method static Builder|Cih whereUserLancamentoId($value)
 * @method static Builder|Cih whereUserRhId($value)
 * @method static Builder|Cih whereVariosColaboradores($value)
 * @method static Builder|Cih withTrashed()
 * @method static Builder|Cih withoutTrashed()
 * @mixin \Eloquent
 */
class Cih extends Model
{
    use HasFactory, LogsActivity, TenantTrait, SoftDeletes;

    protected static $logFillable = true;
    protected static $logName = 'cih';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    public const CONFIG_CENTRO_DE_CUSTO = "centro_de_custo";
    public const CONFI_AREA = "area";

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = "";
    }

    protected $fillable = [
        'tag_id',
        'gestor_id', #esse
        'outra_tag',
        'feedback_id',
        'cliente_id',
        'area_id',
        'outra_area',
        'varios_colaboradores',
        'colaboradores_avulso',
        'user_lancamento_id', #esse
        'obs_lancamento',
        'data_lancamento',
        'acao',
        'user_aprovacao_id', #esse
        'obs_aprovacao',
        'data_aprovacao',
        'status',
        'user_rh_id',
        'resposta_rh',
        'obs_rh',
        'data_aprovacao_rh',
        'empresa_id',
        'centro_custo_id',
        'user_deletou_id'
    ];

    protected $casts = [
        'id' => 'int',
        'tag_id' => 'int',
        'gestor_id' => 'int',
        'outra_tag' => 'string',
        'feedback_id' => 'int',
        'area_id' => 'int',
        'outra_area' => 'string',
        'varios_colaboradores' => 'boolean',
        'colaboradores_avulso' => 'string',
        'cliente' => 'int',
        'user_lancamento_id' => 'int',
        'obs_lancamento' => 'string',
        'data_lancamento' => 'string',
        'acao' => 'string',
        'user_aprovacao_id' => 'int',
        'obs_aprovacao' => 'string',
        'data_aprovacao' => 'string',
        'status' => 'string',
        'user_rh_id' => 'int',
        'resposta_rh' => 'string',
        'obs_rh' => 'string',
        'data_aprovacao_rh' => 'string',
        'created_at' => 'datetime:d/m/Y à\s H:i\h',
        'updated_at' => 'datetime:d/m/Y à\s H:i\h',
        'empresa_id' => 'int',
        'centro_custo_id' => 'int',
        'user_deletou_id' => 'int'
    ];

    protected $appends = ['data_criacao'];

    public function getDataCriacaoAttribute()
    {
        return (new DataHora($this->created_at))->dataHoraCompleta();
    }

    /**
     * Scope a query para mostrar apenas cihs vinculados ao user autenticado.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVinculados($query)
    {
        return $query->where('gestor_id', auth()->user()->id)
            ->orWhere('user_lancamento_id', auth()->user()->id)
            ->orWhere('user_aprovacao_id', auth()->user()->id);
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function getDataLancamentoAttribute($value)
    {
        if ($value) {
            $data = new DataHora($this->attributes['data_lancamento']);
            return $data->dataHoraCompleta();
        }
    }

    //Modificador ->data_fim
    public function setDataLancamentoAttribute($value)
    {
        if ($value) {
            $data = new DataHora($value);
            $this->attributes['data_lancamento'] = $data->dataHoraInsert();
        }
    }

    public function getDataAprovacaoAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($this->attributes['data_aprovacao']);
            return $data->dataHoraCompleta();
        } else {
            return null;
        }
    }

    //Modificador ->data_fim
    public function setDataAprovacaoAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($value);
            $this->attributes['data_aprovacao'] = $data->dataHoraInsert();
        } else {
            $this->attributes['data_aprovacao'] = null;
        }
    }

    public function getDataAprovacaoRhAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($this->attributes['data_aprovacao_rh']);
            return $data->dataHoraCompleta();
        } else {
            return null;
        }
    }

    //Modificador ->data_fim
    public function setDataAprovacaoRhAttribute($value)
    {
        if (!is_null($value)) {
            $data = new DataHora($value);
            $this->attributes['data_aprovacao_rh'] = $data->dataHoraInsert();
        } else {
            $this->attributes['data_aprovacao_rh'] = null;
        }
    }

    public function Cliente()
    {
        return $this->hasOne(Cliente::class, 'id', 'cliente_id');
    }

    public function Tag()
    {
        return $this->hasOne(CihTag::class, 'id', 'tag_id');
    }

    public function Colaborador()
    {
        return $this->hasOne(FeedbackCurriculo::class, 'id', 'feedback_id');
    }

    public function GestorAprovacao()
    {
        return $this->hasOne(User::class, 'id', 'gestor_id');
    }

    public function ResponsavelLancamento()
    {
        return $this->hasOne(User::class, 'id', 'user_lancamento_id');
    }

    public function Area()
    {
        return $this->hasOne(AreaEtiqueta::class, 'id', 'area_id');
    }

    public function CentroDeCusto()
    {
        return $this->hasOne(CentroCusto::class, 'id', 'centro_custo_id');
    }

    public function ResponsavelAprovacao()
    {
        return $this->hasOne(User::class, 'id', 'user_aprovacao_id');
    }

    public function RhAprovacao()
    {
        return $this->hasOne(User::class, 'id', 'user_rh_id');
    }

    public function Anexos()
    {
        return $this->belongsToMany(Arquivo::class, 'cih_evidencia', 'cih_id', 'arquivo_id');
    }

    public function Empresa()
    {
        return $this->hasOne(User::class, 'id', 'empresa_id');
    }

    public function Colaboradores()
    {
        return $this->belongsToMany(FeedbackCurriculo::class, 'cih_feedback', 'cih_id', 'feedback_id')
            ->select(['id', 'curriculo_id', 'vagas_abertas_id'])->with('Curriculo:id,nome,rg,orgao_expeditor,nascimento', 'Admissao:id,feedback_id,cargo');
    }

    public function CihFeedbacks()
    {
        return $this->belongsToMany(FeedbackCurriculo::class, 'cih_feedback', 'feedback_id', 'cih_id');
    }
}
