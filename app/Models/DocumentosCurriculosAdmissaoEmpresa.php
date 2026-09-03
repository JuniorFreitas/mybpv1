<?php

namespace App\Models;
use App\Support\DocumentoPreadmissaoDescricaoSanitizer;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

/**
 * App\Models\DocumentosCurriculosAdmissaoEmpresa
 *
 * @property int $id
 * @property int $empresa_id
 * @property int|null $categoria_id
 * @property string $label
 * @property string|null $metodo
 * @property string|null $descricao
 * @property string $tipo
 * @property string|null $url_arquivo
 * @property array|null $configuracoes
 * @property int $ordem
 * @property bool $ativo
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosAdmissaoEmpresa newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosAdmissaoEmpresa newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosAdmissaoEmpresa query()
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosAdmissaoEmpresa whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosAdmissaoEmpresa whereCategoriaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosAdmissaoEmpresa whereConfiguracoes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosAdmissaoEmpresa whereDescricao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosAdmissaoEmpresa whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosAdmissaoEmpresa whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosAdmissaoEmpresa whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosAdmissaoEmpresa whereMetodo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosAdmissaoEmpresa whereOrdem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosAdmissaoEmpresa whereTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DocumentosCurriculosAdmissaoEmpresa whereUrlArquivo($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @mixin \Eloquent
 */
class DocumentosCurriculosAdmissaoEmpresa extends Model
{
    use LogsActivity, HasActivitylogOptions, HasFactory;

    protected static $logName = 'DocumentosCurriculosAdmissaoEmpresa';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

    public const CACHE_KEY_PREFIX = 'docAdmEmpresa_';
    public const CACHE_TTL_HORAS = 168;

    protected $table = 'documentos_curriculos_adm_empresa';

    public $timestamps = false;

    protected $fillable = [
        'empresa_id',
        'categoria_id',
        'label',
        'metodo',
        'descricao',
        'tipo',
        'url_arquivo',
        'configuracoes',
        'ordem',
        'ativo',
    ];

    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'categoria_id' => 'int',
        'label' => 'string',
        'metodo' => 'string',
        'descricao' => 'string',
        'tipo' => 'string',
        'url_arquivo' => 'string',
        'configuracoes' => 'json',
        'ordem' => 'int',
        'ativo' => 'boolean',
    ];

    public function getConfiguracoesAttribute($value)
    {
        $configuracoes = json_decode($value, true);

        return array_merge([
            'obrigatorio' => false,
            'apenas_img' => false,
            'apenas_pdf' => false,
            'apenas_pdf_img' => false,
            'multiple' => false,
            'min' => 1,
            'max' => 1,
            'sogestao' => false,
        ], is_array($configuracoes) ? $configuracoes : []);
    }

    public function getDescricaoAttribute($value): ?string
    {
        return DocumentoPreadmissaoDescricaoSanitizer::sanitize($value);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(DocumentosCurriculosCatAdmissaoEmpresa::class, 'categoria_id');
    }

    public static function cacheKey(int $empresaId): string
    {
        return self::CACHE_KEY_PREFIX . $empresaId;
    }

    public static function limparCache(int $empresaId): void
    {
        Cache::forget(self::cacheKey($empresaId));
    }

    public static function getDocumentoCurriculoAdmissaoEmpresa($empresa_id)
    {
        app(\App\Services\Preadmissao\DocumentoPreadmissaoCadastroService::class)
            ->garantirPadraoSistema((int) $empresa_id);

        $key = self::cacheKey((int) $empresa_id);
        $docAdmEmpresa = Cache::get($key);

        if (!$docAdmEmpresa) {
            $docAdmEmpresa = self::query()
                ->where('documentos_curriculos_adm_empresa.empresa_id', $empresa_id)
                ->whereAtivo(true)
                ->with('categoria')
                ->orderBy('documentos_curriculos_adm_empresa.categoria_id')
                ->orderBy('documentos_curriculos_adm_empresa.ordem')
                ->get()
                ->transform(function ($doc) {
                    $labelCategoria = $doc->getRelation('categoria')?->label;
                    $doc->unsetRelation('categoria');
                    $doc->setAttribute('categoria', $labelCategoria);
                    return $doc;
                });
            Cache::put($key, $docAdmEmpresa, now()->addHours(self::CACHE_TTL_HORAS));
        }

        return $docAdmEmpresa;
    }
}
