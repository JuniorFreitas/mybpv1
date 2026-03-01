<?php

namespace App\Models;

use App\Models\Pivot\TreinamentoVencimento;
use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\Vencimento
 *
 * @property int $id
 * @property string $label
 * @property string|null $descricao
 * @property int|null $prazo_parada
 * @property int|null $prazo_fixo
 * @property int|null $ordem
 * @property bool $ativo
 * @property int|null $empresa_id
 * @property int|null $segmento_treinamento_id
 * @property string|null $label_reduzida
 * @property bool|null $exibir_na_carteira
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|Vencimento newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Vencimento newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Vencimento query()
 * @method static \Illuminate\Database\Eloquent\Builder|Vencimento whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vencimento whereDescricao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vencimento whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vencimento whereExibirNaCarteira($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vencimento whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vencimento whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vencimento whereLabelReduzida($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vencimento whereOrdem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vencimento wherePrazoFixo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vencimento wherePrazoParada($value)
 * @mixin \Eloquent
 */
class Vencimento extends Model
{
    use HasFactory, LogsActivity;
    use TenantTrait;

    protected static $logFillable = true;
    protected static $logName = 'vencimento';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = "";
    }

    public $timestamps = false;
    protected $fillable = [
        'label',
        'label_reduzida',
        'exibir_na_carteira',
        'descricao',
        'prazo_parada',
        'prazo_fixo',
        'ativo',
        'ordem',
        'empresa_id',
        'segmento_treinamento_id',
    ];

    protected $casts = [
        'id' => 'int',
        'label' => 'string',
        'label_reduzida' => 'string',
        'exibir_na_carteira' => 'boolean',
        'descricao' => 'string',
        'prazo_parada' => 'int',
        'prazo_fixo' => 'int',
        'ativo' => 'boolean',
        'ordem' => 'int',
        'empresa_id' => 'int',
        'segmento_treinamento_id' => 'int',
    ];

    public static function cacheKey($empresaId): string
    {
        $empresaId = $empresaId ?: 'global';
        return "vencimentos_ativos_empresa_{$empresaId}";
    }

    protected static function booted()
    {
        static::saved(function ($model) {
            Cache::forget(self::cacheKey($model->empresa_id));
        });

        static::deleted(function ($model) {
            Cache::forget(self::cacheKey($model->empresa_id));
        });
    }

    public function SegmentoTreinamento()
    {
        return $this->belongsTo(SegmentoTreinamento::class, 'segmento_treinamento_id', 'id');
    }

    public function arquivosVencimentos()
    {
        return $this->hasManyThrough(
            Arquivo::class,
            TreinamentoVencimento::class,
            'vencimento_id',
            'id',
            'id',
            'arquivo_id'
        );
    }
}
