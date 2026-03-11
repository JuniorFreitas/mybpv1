<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Testemunhal
 *
 * @property int $id
 * @property string $nome
 * @property string|null $subtitulo
 * @property string $texto
 * @property bool $ativo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Arquivo> $Anexo
 * @property-read int|null $anexo_count
 * @method static \Illuminate\Database\Eloquent\Builder|Testemunhal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Testemunhal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Testemunhal query()
 * @method static \Illuminate\Database\Eloquent\Builder|Testemunhal whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Testemunhal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Testemunhal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Testemunhal whereNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Testemunhal whereSubtitulo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Testemunhal whereTexto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Testemunhal whereUpdatedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @mixin \Eloquent
 */
class Testemunhal extends Model
{
    use LogsActivity, HasActivitylogOptions, HasFactory;

    protected static $logName = 'Testemunhal';

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
        'subtitulo',
        'texto',
        'ativo',
    ];
    protected $casts = [
        'id' => 'int',
        'nome' => 'string',
        'subtitulo' => 'string',
        'texto' => 'string',
        'ativo' => 'boolean',
    ];

    public function Anexo()
    {
        return $this->belongsToMany(Arquivo::class, 'pivot_testemunhals', 'testemunhal_id', 'arquivo_id');
    }
}
