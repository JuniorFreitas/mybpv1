<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
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
 * @property-read \App\Models\SimuladoVaga $SimuladoVaga
 * @property-read mixed $slug
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Simulado newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Simulado newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Simulado query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Simulado whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Simulado whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Simulado whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Simulado whereTitulo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Simulado whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SimuladoPergunta[] $Perguntas
 * @property-read int|null $perguntas_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Activitylog\Models\Activity[] $activities
 * @property-read int|null $activities_count
 * @property-read mixed $qnt_questoes
 */
class Simulado extends Model
{
    use LogsActivity;

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

    protected $fillable = ['titulo', 'ativo'];
    protected $casts = ['id' => 'int', 'titulo' => 'string', 'ativo' => 'boolean'];

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
