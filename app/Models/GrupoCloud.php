<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\GrupoCloud
 *
 * @property int $id
 * @property string $nome
 * @property string $descricao
 * @property bool $ativo
 * @property int|null $empresa_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $Users
 * @property-read int|null $users_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $Usuarios
 * @property-read int|null $usuarios_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\HabilidadeCloud> $habilidades
 * @property-read int|null $habilidades_count
 * @method static \Illuminate\Database\Eloquent\Builder|GrupoCloud newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GrupoCloud newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GrupoCloud query()
 * @method static \Illuminate\Database\Eloquent\Builder|GrupoCloud whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GrupoCloud whereDescricao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GrupoCloud whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GrupoCloud whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GrupoCloud whereNome($value)
 * @mixin \Eloquent
 */
class GrupoCloud extends Model
{
    use HasFactory, LogsActivity;
    use TenantTrait;

    protected static $logFillable = true;
    protected static $logName = 'grupo_cloud';
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

    protected $fillable = [
        'nome', 'empresa_id', 'descricao', 'ativo'
    ];
    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'nome' => 'string',
        'descricao' => 'string',
        'ativo' => 'boolean',
    ];

    const GRUPOADMIN = 1;
    const GRUPOADMINFINANCEIRO = 11;

    public function usesTimestamps(): bool
    {
        return false;
    }

    //return $this->belongsToMany(Arquivo::class, 'foto_admissaos', 'curriculo_id', 'arquivo_id');
    public function habilidades()
    {
        return $this->belongsToMany(HabilidadeCloud::class, 'grupo_habilidade_cloud', 'grupo_cloud_id', 'habilidade_cloud_id');
    }

    public function Users(){
        return $this->belongsToMany(User::class, 'user_grupo_cloud', 'grupo_cloud_id', 'user_id');
    }

    public function Usuarios()
    {
        return $this->hasMany(User::class, 'grupo_cloud_id', 'id');
    }

}
