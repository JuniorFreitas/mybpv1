<?php

namespace App\Models;

use App\Scopes\ScopeEmpresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\CategoriaPlanoConta
 *
 * @property int $id
 * @property string $descricao
 * @property bool $ativo
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|CategoriaPlanoConta newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CategoriaPlanoConta newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CategoriaPlanoConta query()
 * @method static \Illuminate\Database\Eloquent\Builder|CategoriaPlanoConta whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoriaPlanoConta whereDescricao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoriaPlanoConta whereId($value)
 * @mixin \Eloquent
 * @property int|null $cliente_id
 * @method static \Illuminate\Database\Eloquent\Builder|CategoriaPlanoConta whereClienteId($value)
 * @property int $empresa_id
 * @method static \Illuminate\Database\Eloquent\Builder|CategoriaPlanoConta whereEmpresaId($value)
 */
class CategoriaPlanoConta extends Model
{
    use HasFactory, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'CategoriaPlanoConta';
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

    protected $table = 'categoria_plano_contas';

    protected $fillable = [
        'descricao',
        'ativo',
    ];

    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'descricao' => 'string',
        'ativo' => 'boolean',
    ];

    public $timestamps=false;

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->empresa_id = auth()->user()->empresa_id;
        });

        static::updating(function ($model) {
            $model->empresa_id = auth()->user()->empresa_id;
        });

        static::addGlobalScope(new ScopeEmpresa());
    }
}
