<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\Vaga
 *
 * @property int $id
 * @property int $categoria_id
 * @property string $nome
 * @property bool $ativo
 * @property-read \App\Models\CategoriaVagas|null $Categoria
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|Vaga newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Vaga newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Vaga query()
 * @method static \Illuminate\Database\Eloquent\Builder|Vaga whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vaga whereCategoriaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vaga whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vaga whereNome($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SimuladoVaga[] $SimuladoVaga
 * @property-read int|null $simulado_vaga_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Etapas[] $EtapaStatus
 * @property-read int|null $etapa_status_count
 * @property int|null $empresa_id
 * @method static \Illuminate\Database\Eloquent\Builder|Vaga whereEmpresaId($value)
 * @property-read \App\Models\Cliente|null $Empresa
 * @property-read \App\Models\VagasAbertas|null $VagaAberta
 */
class Vaga extends Model
{
    use HasFactory, LogsActivity, TenantTrait;

    protected static $logFillable = true;
    protected static $logName = 'vaga';
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

    protected $table = 'vagas';
    protected $fillable = ['categoria_id', 'nome', 'ativo', 'empresa_id'];
    protected $casts = ['id' => 'int', 'categoria_id' => 'int', 'nome' => 'string', 'ativo' => 'boolean', 'empresa_id' => 'int'];

    public function usesTimestamps()
    {
        return false;
    }

    public function Categoria()
    {
        return $this->hasOne(CategoriaVagas::class, 'id', 'categoria_id');
    }

    public function SimuladoVaga()
    {
        return $this->hasMany(SimuladoVaga::class, 'vaga_id', 'id');
    }

    public function EtapaStatus()
    {
        return $this->hasMany(Etapas::class, 'vaga_id', 'id')->orderByDesc('updated_at');
    }

    public function Empresa()
    {
        return $this->hasOne(User::class, 'id', 'empresa_id');
    }

    public function VagaAberta()
    {
        return $this->hasOne(VagasAbertas::class, 'vaga_id', 'id');
    }

}
