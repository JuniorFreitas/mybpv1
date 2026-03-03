<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;

/**
 * App\Models\Galeria
 *
 * @property int $id
 * @property string $titulo
 * @property string|null $descricao
 * @property int|null $ordem
 * @property bool $ativo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $Fotos
 * @property-read int|null $fotos_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|Galeria newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Galeria newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Galeria query()
 * @method static \Illuminate\Database\Eloquent\Builder|Galeria whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Galeria whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Galeria whereDescricao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Galeria whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Galeria whereOrdem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Galeria whereTitulo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Galeria whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Galeria extends Model
{
    use HasFactory, LogsActivity, HasActivitylogOptions;

    protected static $logFillable = true;
    protected static $logName = 'galeria';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    public function getDescriptionForEvent(string $eventName): string {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = "";
    }

    protected $fillable = [
        'titulo',
        'descricao',
        'ordem',
        'ativo',
    ];

    protected $casts = [
        'id' => 'int',
        'titulo' => 'string',
        'descricao' => 'string',
        'ordem' => 'int',
        'ativo' => 'boolean',
    ];

    public function Fotos() {
        return $this->belongsToMany(Arquivo::class, 'foto_galerias', 'galeria_id', 'arquivo_id')->withPivot(['ordem'])->orderBy('ordem');
    }
}
