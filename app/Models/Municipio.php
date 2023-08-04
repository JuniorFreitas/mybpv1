<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\Municipio
 *
 * @property int $id
 * @property string $nome
 * @property string $uf
 * @property bool $capital
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\VagasAbertas[] $VagasAbertas
 * @property-read int|null $vagas_abertas_count
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|Municipio newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Municipio newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Municipio query()
 * @method static \Illuminate\Database\Eloquent\Builder|Municipio whereCapital($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Municipio whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Municipio whereNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Municipio whereUf($value)
 * @mixin \Eloquent
 */
class Municipio extends Model
{
    use HasFactory, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'municipio';
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

    protected $table = 'municipios';
    protected $fillable = ['nome', 'uf', 'capital'];
    protected $casts = [
        'id' => 'int',
        'nome' => 'string',
        'uf' => 'string',
        'capital' => 'boolean',
    ];

    public function usesTimestamps(): bool
    {
        return false;
    }

    public function VagasAbertas()
    {
        return $this->hasMany(VagasAbertas::class, 'municipio_id', 'id');
    }

    public static function todosEstados()
    {
        if (!\Cache::get("todosEstados")) {
            \Cache::rememberForever("todosEstados", function () {
                return Municipio::select('uf')->distinct('uf')->get();
            });
        }
        return Municipio::select('uf')->distinct('uf')->get();
    }

    protected static function booted()
    {
        static::created(function ($model) {
            \Cache::forget("todosEstados");
        });
        static::updated(function ($model) {
            \Cache::forget("todosEstados");
        });
    }
}
