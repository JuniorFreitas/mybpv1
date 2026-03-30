<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use MasterTag\DataHora;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;

/**
 * App\Models\Avaliacao
 *
 * @property int $id
 * @property int $avaliacao_tipo_id
 * @property int $empresa_id
 * @property string $titulo
 * @property string|null $data_inicio_prazo
 * @property string|null $data_fim_prazo
 * @property string $status
 * @property bool $ativo
 * @property-read \App\Models\AvaliacaoTipo $AvaliacaoTipo
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|Avaliacao newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Avaliacao newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Avaliacao query()
 * @method static \Illuminate\Database\Eloquent\Builder|Avaliacao whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Avaliacao whereAvaliacaoTipoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Avaliacao whereDataFimPrazo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Avaliacao whereDataInicioPrazo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Avaliacao whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Avaliacao whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Avaliacao whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Avaliacao whereTitulo($value)
 * @property bool $auto_avaliacao
 * @property mixed|null $fluxo
 * @method static \Illuminate\Database\Eloquent\Builder|Avaliacao whereAutoAvaliacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Avaliacao whereFluxo($value)
 * @property int $ano_avaliacao Ano da avaliação
 * @method static \Illuminate\Database\Eloquent\Builder|Avaliacao whereAnoAvaliacao($value)
 * @property bool $tipo_pj
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AvaliacaoFeedback> $AvaliacaoFeedbacks
 * @property-read int|null $avaliacao_feedbacks_count
 * @method static \Illuminate\Database\Eloquent\Builder|Avaliacao whereTipoPj($value)
 * @mixin \Eloquent
 */
class Avaliacao extends Model
{
    use TenantTrait, LogsActivity, HasActivitylogOptions;

    protected static bool $logFillable = true;
    protected static string $logName = 'avaliacoes';
    protected static bool $logOnlyDirty = true;
    protected static bool $submitEmptyLogs = false;

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        $activity->descricao = "";
    }


    protected $table = "avaliacoes";

    protected $fillable = [
        'titulo',
        'ano_avaliacao',
        'avaliacao_tipo_id',
        'data_inicio_prazo',
        'data_fim_prazo',
        'empresa_id',
        'status',
        'ativo',
        'auto_avaliacao',
        'fluxo',
        'tipo_pj',
        'mostrar_notas_avaliador_final'
    ];

    protected $casts = [
        'id' => 'int',
        'avaliacao_tipo_id' => 'int',
        'titulo' => 'string',
        'ano_avaliacao' => 'int',
        'data_inicio_prazo' => 'string',
        'data_fim_prazo' => 'string',
        'empresa_id' => 'int',
        'status' => 'string',
        'ativo' => 'boolean',
        'auto_avaliacao' => 'boolean',
        'fluxo' => 'json',
        'tipo_pj' => 'boolean',
        'mostrar_notas_avaliador_final' => 'boolean'
    ];

    public $timestamps = false;

    const STATUS_AGUARDANDO_INICIO = 'Aguardando Inicio';
    const STATUS_ABERTA = 'Aberta';
    const STATUS_ENCERRADA = 'Encerrada';

    const LISTA_STATUS = [
        self::STATUS_AGUARDANDO_INICIO,
        self::STATUS_ABERTA,
        self::STATUS_ENCERRADA
    ];


    public function AvaliacaoTipo()
    {
        return $this->belongsTo(AvaliacaoTipo::class, 'avaliacao_tipo_id', 'id');
    }

    public function AvaliacaoFeedbacks()
    {
        return $this->hasMany(AvaliacaoFeedback::class, 'avaliacao_id', 'id');
    }

    //Acessor ->data_inicio
    public function getDataInicioPrazoAttribute($value)
    {
        if ($value) {
            return (new DataHora($this->attributes['data_inicio_prazo']))->dataCompleta();
        }
    }

    //Modificador ->data_inicio
    public function setDataInicioPrazoAttribute($value)
    {
        if ($value) {
            $this->attributes['data_inicio_prazo'] = (new DataHora($value . ' 00:00:00'))->dataHoraInsert();
        }
    }

    //Acessor ->data_fim
    public function getDataFimPrazoAttribute($value)
    {
        if ($value) {
            return (new DataHora($this->attributes['data_fim_prazo']))->dataCompleta();
        }
    }

    //Modificador ->data_fim
    public function setDataFimPrazoAttribute($value)
    {
        if ($value) {
            $this->attributes['data_fim_prazo'] = (new DataHora($value . ' 23:59:59'))->dataHoraInsert();
        }
    }

    /**
     * @param $empresaId
     * @return Repository|JsonResponse|mixed|string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function listaTodasAvaliacoesAgrupadaAno($empresaId)
    {
        $empresaId = $this->getEmpresaId($empresaId);
        $cache_key = "lista_av_grp_ano_{$empresaId}";

//        cache()->forget($cache_key);

        if (!$empresaId) {
            return response()->json(['msg' => 'Empresa não informada'], 400);
        }

        if (is_null(cache()->get($cache_key))) {
            $todasAvaliacoesAgrupadaAno = Avaliacao::select([
                'id',
                'avaliacao_tipo_id',
                'empresa_id',
                'titulo',
                'ano_avaliacao',
                'data_inicio_prazo',
                'data_fim_prazo',
                'status',
                'ativo',
                'auto_avaliacao',
                'tipo_pj'
            ])->with('AvaliacaoTipo')
                ->get()
                ->groupBy('ano_avaliacao');

            cache()->put($cache_key,
                collect($todasAvaliacoesAgrupadaAno
                ), now()->addDays(7));
        }

        return cache()->get($cache_key);

    }


    /**
     * @param $empresaId
     * @return int|null
     */
    private function getEmpresaId($empresaId): ?int
    {
        return !auth()->check() ? $empresaId : auth()->user()->empresa_id;
    }

    public static function fluxoAvaliacao($avaliacao_id)
    {
        $avaliacao = (new self())::select(['id', 'auto_avaliacao', 'fluxo'])->where('id', $avaliacao_id)->first();

        return collect($avaliacao->fluxo)->map(function ($item) {
            $avaliador_tipo = $item['principal'] ? ' (Avaliador Final)' : '';
            return [
                'id' => $item['id'],
                'label' => $item['label'] . $avaliador_tipo,
                'principal' => $item['principal'],
            ];
        })->when($avaliacao->auto_avaliacao, function ($collection) {
            return $collection->prepend(['id' => 0, 'label' => 'Auto Avaliação', 'principal' => false]);
        });

    }

    /**
     * @param $empresaId
     * @return void
     * @throws \Exception
     */
    public function forgetsCache($empresaId): void
    {
        $cache_key = "lista_av_grp_ano_{$empresaId}";
        cache()->forget($cache_key);
        $this->listaTodasAvaliacoesAgrupadaAno($empresaId);
    }

    /**
     * @return void
     */
    protected static function booted(): void
    {
        static::created(function ($model) {
            (new self())->forgetsCache($model->empresa_id);
        });

        static::updated(function ($model) {
            (new self())->forgetsCache($model->empresa_id);
        });
    }
}
