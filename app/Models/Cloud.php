<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use App\Models\Concerns\HasActivitylogOptions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\Cloud
 *
 * @property int $id
 * @property int $empresa_id
 * @property string $nome
 * @property string $slug
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property bool $ativo
 * @property-read \App\Models\Cliente $Empresa
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ItensCloud> $Itens
 * @property-read int|null $itens_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ItensCloud> $Raiz
 * @property-read int|null $raiz_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $Usuarios
 * @property-read int|null $usuarios_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|Cloud newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cloud newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cloud query()
 * @method static \Illuminate\Database\Eloquent\Builder|Cloud whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cloud whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cloud whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cloud whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cloud whereNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cloud whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Cloud extends Model
{
    use HasFactory, LogsActivity, HasActivitylogOptions, TenantTrait;


    protected static $logFillable = true;
    protected static $logName = 'cloud';
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

    protected $fillable = ['nome', 'slug', 'empresa_id', 'ativo'];
    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'nome' => 'string',
        'slug' => 'string',
        'ativo' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Cloud $cloud) {
            if ($cloud->isDirty('nome') || blank($cloud->slug)) {
                $empresaId = (int) ($cloud->empresa_id ?: auth()->user()?->empresa_id);
                $cloud->slug = static::gerarSlugUnico(
                    (string) $cloud->nome,
                    $empresaId,
                    $cloud->exists ? (int) $cloud->id : null
                );
            }
        });
    }

    /**
     * Slug amigável (labels de pasta / nome do cloud).
     */
    public static function slugify(string $texto): string
    {
        $slug = Str::slug($texto);

        return $slug !== '' ? $slug : 'item';
    }

    /**
     * Gera slug único por empresa, com sufixo numérico se necessário.
     */
    public static function gerarSlugUnico(string $nome, int $empresaId, ?int $ignorarId = null): string
    {
        $base = static::slugify($nome);
        $slug = $base;
        $sufixo = 2;

        while (
            static::query()
                ->where('empresa_id', $empresaId)
                ->where('slug', $slug)
                ->when($ignorarId, fn ($q) => $q->where('id', '!=', $ignorarId))
                ->exists()
        ) {
            $slug = $base . '-' . $sufixo;
            $sufixo++;
        }

        return $slug;
    }

    public function Empresa()
    {
        return $this->belongsTo(Cliente::class, 'empresa_id');
    }

    public function Itens()
    {
        return $this->hasMany(ItensCloud::class, 'cloud_id', 'id');
    }

    public function Raiz()
    {
        return $this->hasMany(ItensCloud::class, 'cloud_id', 'id')->whereNull('pertence');
    }

    public function Usuarios()
    {
        return $this->belongsToMany(User::class, 'user_clouds', 'cloud_id', 'user_id')->select(['id', 'nome']);
    }

    /**
     * Verifica se o usuário autenticado é membro deste Cloud e se ele está ativo.
     */
    public function usuarioTemAcesso(?User $user = null): bool
    {
        $user = $user ?? auth()->user();
        if (!$user || !$this->ativo) {
            return false;
        }

        return $user->Clouds()
            ->where('clouds.id', $this->id)
            ->exists();
    }

    /**
     * Localiza Cloud autorizado para o usuário ou aborta (404/403).
     * Protege contra IDOR por ID na URL.
     */
    public static function encontrarAutorizadoOuAbortar(int|string $cloudId): self
    {
        $cloud = static::query()->whereKey($cloudId)->first();
        if (!$cloud) {
            abort(404);
        }

        if (!$cloud->usuarioTemAcesso()) {
            abort(403, 'Sem permissão para acessar este Cloud');
        }

        return $cloud;
    }

    /**
     * Localiza Cloud autorizado pelo slug ou aborta (404/403).
     */
    public static function encontrarAutorizadoPorSlugOuAbortar(string $slug): self
    {
        $cloud = static::query()->where('slug', $slug)->first();
        if (!$cloud) {
            abort(404);
        }

        if (!$cloud->usuarioTemAcesso()) {
            abort(403, 'Sem permissão para acessar este Cloud');
        }

        return $cloud;
    }

    /**
     * Sincroniza membros do grupo Administradores em todos os Clouds da empresa:
     * adiciona nos clouds e remove dos clouds quando sai do grupo.
     *
     * @param  iterable<int>  $usuariosAdicionados
     * @param  iterable<int>  $usuariosRemovidos
     */
    public static function sincronizarMembrosAdministradores(int $empresaId, iterable $usuariosAdicionados = [], iterable $usuariosRemovidos = []): void
    {
        $cloudIds = static::query()
            ->where('empresa_id', $empresaId)
            ->pluck('id');

        if ($cloudIds->isEmpty()) {
            return;
        }

        $adicionados = collect($usuariosAdicionados)
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $removidos = collect($usuariosRemovidos)
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($removidos->isNotEmpty()) {
            \DB::table('user_clouds')
                ->whereIn('cloud_id', $cloudIds)
                ->whereIn('user_id', $removidos)
                ->delete();
        }

        if ($adicionados->isEmpty()) {
            return;
        }

        $existentes = \DB::table('user_clouds')
            ->whereIn('cloud_id', $cloudIds)
            ->whereIn('user_id', $adicionados)
            ->get(['cloud_id', 'user_id'])
            ->mapWithKeys(fn ($row) => ["{$row->cloud_id}:{$row->user_id}" => true]);

        $novos = [];
        foreach ($cloudIds as $cloudId) {
            foreach ($adicionados as $userId) {
                $chave = "{$cloudId}:{$userId}";
                if (!isset($existentes[$chave])) {
                    $novos[] = [
                        'cloud_id' => $cloudId,
                        'user_id' => $userId,
                    ];
                }
            }
        }

        if (!empty($novos)) {
            foreach (array_chunk($novos, 500) as $lote) {
                \DB::table('user_clouds')->insert($lote);
            }
        }
    }

    public static function todosCloudsAdminAtivo($usuarioId, $empresaId)
    {
        return Cloud::where('empresa_id', $empresaId)->where('ativo', true)->get();
    }

}
