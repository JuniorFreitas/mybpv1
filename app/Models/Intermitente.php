<?php

namespace App\Models;

use App\Models\User;
use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\Intermitente
 *
 * @property int $id
 * @property int $feedback_id somente quem foi admitido
 * @property int $cliente_id Cliente empresa
 * @property int $user_lancamento_id Responsavel pelo lançamenro usuario em sessão
 * @property int|null $area_id
 * @property int|null $tipo_id
 * @property string|null $obs_lancamento Responsavel pela aprovação usuario em sessão
 * @property mixed $data_lancamento
 * @property string $acao
 * @property int|null $user_aprovacao_id Responsavel pela aprovação usuario em sessão
 * @property string|null $obs_aprovacao Responsavel pela aprovação usuario em sessão
 * @property mixed|null $data_aprovacao
 * @property string|null $status aberto, aprovado
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Arquivo[] $Anexos
 * @property-read int|null $anexos_count
 * @property-read \App\Models\AreaEtiqueta $Area
 * @property-read \App\Models\Clientes $Cliente
 * @property-read \App\Models\FeedbackCurriculo $Colaborador
 * @property-read \App\Models\User $ResponsavelAprovacao
 * @property-read \App\Models\User $ResponsavelLancamento
 * @property-read \App\Models\IntermitenteTipo $Tipo
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Activitylog\Models\Activity[] $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Intermitente newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Intermitente newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Intermitente query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Intermitente whereAcao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Intermitente whereAreaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Intermitente whereClienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Intermitente whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Intermitente whereDataAprovacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Intermitente whereDataLancamento($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Intermitente whereFeedbackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Intermitente whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Intermitente whereObsAprovacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Intermitente whereObsLancamento($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Intermitente whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Intermitente whereTipoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Intermitente whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Intermitente whereUserAprovacaoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Intermitente whereUserLancamentoId($value)
 * @mixin \Eloquent
 * @property string $encerramento_previsto
 * @property bool|null $devolve_epi
 * @property bool|null $devolve_cracha
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\IntermitenteProrrogacao[] $Prorrogacao
 * @property-read int|null $prorrogacao_count
 * @method static \Illuminate\Database\Eloquent\Builder|Intermitente whereDevolveCracha($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Intermitente whereDevolveEpi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Intermitente whereEncerramentoPrevisto($value)
 * @property int $empresa_id
 * @method static \Illuminate\Database\Eloquent\Builder|Intermitente whereEmpresaId($value)
 * @property string $hash_colaborador
 * @property string|null $resposta_colaborador
 * @property string|null $data_resposta_colaborador
 * @property int|null $centro_custo_id
 * @property int|null $prazo_resposta
 * @property string|null $prazo_resposta_expiracao
 * @property-read \App\Models\CentroCusto|null $CentroDeCusto
 * @method static \Illuminate\Database\Eloquent\Builder|Intermitente whereCentroCustoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Intermitente whereDataRespostaColaborador($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Intermitente whereHashColaborador($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Intermitente wherePrazoResposta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Intermitente wherePrazoRespostaExpiracao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Intermitente whereRespostaColaborador($value)
 */
class Intermitente extends Model
{
    use LogsActivity, TenantTrait;

    protected static $logFillable = true;
    protected static $logName = 'intermitente';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    public function getDescriptionForEvent(string $eventName)
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = "";
    }

    protected $fillable = [
        'tipo_id',
        'feedback_id',
        'cliente_id',
        'area_id',
        'centro_custo_id',
        'user_lancamento_id',
        'obs_lancamento',
        'data_lancamento',
        'encerramento_previsto',
        'acao',
        'user_aprovacao_id',
        'obs_aprovacao',
        'data_aprovacao',
        'status',
        'devolve_epi',
        'devolve_cracha',
        'empresa_id',
        'hash_colaborador',
        'resposta_colaborador',
        'data_resposta_colaborador',
        'prazo_resposta',
        'prazo_resposta_expiracao',
    ];

    protected $casts = [
        'id' => 'int',
        'tipo_id' => 'int',
        'feedback_id' => 'int',
        'area_id' => 'int',
        'centro_custo_id' => 'int',
        'cliente_id' => 'int',
        'user_lancamento_id' => 'int',
        'obs_lancamento' => 'string',
        'data_lancamento' => 'string',
        'acao' => 'string',
        'user_aprovacao_id' => 'int',
        'obs_aprovacao' => 'string',
        'data_aprovacao' => 'string',
        'status' => 'string',
        'devolve_epi' => 'boolean',
        'devolve_cracha' => 'boolean',
        'empresa_id' => 'int',
        'hash_colaborador' => 'string',
        'resposta_colaborador' => 'string',
        'data_resposta_colaborador' => 'string',
        'prazo_resposta' => 'int',
        'prazo_resposta_expiracao' => 'string',
    ];

    const STATUS_EXPIRADO = 'Expirado';
    const STATUS_ABERTO = 'Aberto';
    const STATUS_ENCERRADO = 'Encerrado';

    const LISTA_STATUS = [
        self::STATUS_ABERTO,
        self::STATUS_ENCERRADO,
        self::STATUS_EXPIRADO
    ];

    public function getDataLancamentoAttribute($value)
    {
        if ($value) {
            $data = new DataHora($this->attributes['data_lancamento']);
            return $data->dataCompleta() . ' às ' . $data->horaCompleta();
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
            return $data->dataCompleta() . ' às ' . $data->horaCompleta();
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

    public function getDataRespostaColaboradorAttribute($value)
    {
        if ($value) {
            $data = new DataHora($this->attributes['data_resposta_colaborador']);
            return $data->dataCompleta() . ' às ' . $data->horaCompleta();
        }
    }

    public function Cliente()
    {
        return $this->hasOne(Cliente::class, 'id', 'cliente_id');
    }

    public function Tipo()
    {
        return $this->hasOne(IntermitenteTipo::class, 'id', 'tipo_id');
    }

    public function Colaborador()
    {
        return $this->hasOne(FeedbackCurriculo::class, 'id', 'feedback_id');
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

    public function Prorrogacao()
    {
        return $this->hasMany(IntermitenteProrrogacao::class, 'intermitente_id', 'id');
    }

    public function Anexos()
    {
        return $this->belongsToMany(Arquivo::class, 'intermitente_evidencias', 'intermitente_id', 'arquivo_id');
    }

    protected static function booted() {
        static::creating(function ($model) {
            $model->prazo_resposta_expiracao = Carbon::now()->addHours($model->prazo_resposta)->addSeconds(0);
        });
    }

}
