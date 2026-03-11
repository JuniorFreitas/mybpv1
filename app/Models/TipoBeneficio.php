<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use App\Scopes\ScopeClientesEmpresa;
use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\TipoBeneficio
 *
 * @property int $id
 * @property string $nome
 * @property int|null $cliente_id
 * @property bool $ativo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $empresa_id
 * @property-read \App\Models\User|null $Empresa
 * @method static \Illuminate\Database\Eloquent\Builder|TipoBeneficio newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TipoBeneficio newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TipoBeneficio query()
 * @method static \Illuminate\Database\Eloquent\Builder|TipoBeneficio whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoBeneficio whereClienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoBeneficio whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoBeneficio whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoBeneficio whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoBeneficio whereNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TipoBeneficio whereUpdatedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @mixin \Eloquent
 */
class TipoBeneficio extends Model
{
    use LogsActivity, HasActivitylogOptions, TenantTrait;

    protected static $logName = 'TipoBeneficio';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

    protected $fillable = [
        'nome',
        'empresa_id',
        'ativo'
    ];

    protected $casts = [
        'nome' => 'string',
        'empresa_id' => 'int',
        'ativo' => 'boolean'
    ];

    protected $table = 'tipo_beneficios';

    public function Empresa()
    {
        return $this->hasOne(User::class, 'id', 'empresa_id');
    }

//    //Scopo de ClienteID (Empresa)
//    protected static function booted()
//    {
//        static::addGlobalScope(new ScopeClientesEmpresa);
//    }
}
