<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;

/**
 * App\Models\CategoriaVagas
 *
 * @property int $id
 * @property string $titulo
 * @property bool $ativo
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|CategoriaVagas newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CategoriaVagas newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CategoriaVagas query()
 * @method static \Illuminate\Database\Eloquent\Builder|CategoriaVagas whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoriaVagas whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoriaVagas whereTitulo($value)
 * @mixin \Eloquent
 */
class CategoriaVagas extends Model
{
    use HasFactory, LogsActivity, HasActivitylogOptions;

    protected static $logFillable = true;
    protected static $logName = 'categoria_vaga';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    public function getDescriptionForEvent(string $eventName): string {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = "";
    }

    protected $fillable = ['titulo', 'ativo'];
    protected $casts = ['id' => 'int', 'titulo' => 'string', 'ativo' => 'boolean'];
    protected $table = 'categoria_vagas';

    public function usesTimestamps() : bool{
        return false;
    }
}
