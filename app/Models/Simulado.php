<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\Simulado
 *
 * @property int $id
 * @property string $titulo
 * @property bool $ativo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $empresa_id
 * @property string|null $tipo_prova
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SimuladoPergunta> $Perguntas
 * @property-read int|null $perguntas_count
 * @property-read \App\Models\SimuladoVaga|null $SimuladoVaga
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read mixed $qnt_questoes
 * @property-read mixed $slug
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Illuminate\Database\Eloquent\Builder|Simulado newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Simulado newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Simulado query()
 * @method static \Illuminate\Database\Eloquent\Builder|Simulado whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Simulado whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Simulado whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Simulado whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Simulado whereTipoProva($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Simulado whereTitulo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Simulado whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Simulado extends Model
{
    use LogsActivity;
    use HasApiTokens;
    use TenantTrait;

    protected static $logFillable = true;
    protected static $logName = 'Simulado';
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
        'titulo',
        'tipo_prova',
        'ativo',
        'empresa_id'
    ];
    protected $casts = [
        'id' => 'int',
        'titulo' => 'string',
        'tipo_prova' => 'string',
        'ativo' => 'boolean',
        'empresa_id' => 'int'
    ];

    protected $appends = ['slug', 'qnt_questoes'];

    public function getSlugAttribute()
    {
        return Str::slug($this->attributes['titulo']);
    }

    public function getQntQuestoesAttribute()
    {
        return $this->Perguntas()->count();
    }

    public function SimuladoVaga()
    {
        return $this->hasOne(SimuladoVaga::class, 'simulado_id', 'id');
    }

    public function Perguntas()
    {
        return $this->hasMany(SimuladoPergunta::class, 'simulado_id', 'id');
    }
}
