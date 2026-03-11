<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use App\Scopes\ScopeEmpresa;
use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Formulario
 *
 * @property int $id
 * @property int|null $empresa_id
 * @property string $titulo
 * @property string|null $descricao
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SetoresFormulario> $Setores
 * @property-read int|null $setores_count
 * @method static \Illuminate\Database\Eloquent\Builder|Formulario newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Formulario newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Formulario query()
 * @method static \Illuminate\Database\Eloquent\Builder|Formulario whereDescricao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Formulario whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Formulario whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Formulario whereTitulo($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @mixin \Eloquent
 */
class Formulario extends Model
{
    use LogsActivity, HasActivitylogOptions, HasFactory, TenantTrait;

    protected static $logName = 'Formulario';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

    const TABELA = 'formularios';
    protected $table = self::TABELA;

    protected $fillable = ['titulo', 'descricao', 'empresa_id'];
    protected $casts = ['id' => 'int', 'titulo' => 'string', 'descricao' => 'string', 'empresa_id' => 'int'];

    public $timestamps = false;

    public function Setores()
    {
        return $this->belongsToMany(SetoresFormulario::class, 'formulario_setores', 'formulario_id', 'setores_id')->orderBy('ordem');
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->empresa_id = auth()->check() ? auth()->user()->empresa_id : $model->empresa_id;
        });

        static::updating(function ($model) {
            $model->empresa_id = auth()->check() ? auth()->user()->empresa_id : $model->empresa_id;
        });

//        static::addGlobalScope(new ScopeEmpresa());
    }
}
