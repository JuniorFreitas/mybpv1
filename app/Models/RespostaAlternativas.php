<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\RespostaAlternativas
 *
 * @property int $id
 * @property int $alternativa_id
 * @property string $label
 * @property bool|null $selecionado Para os checkbox vir marcado
 * @property int|null $link_id
 * @property int $ordem
 * @property int|null $value
 * @method static \Illuminate\Database\Eloquent\Builder|RespostaAlternativas newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RespostaAlternativas newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RespostaAlternativas query()
 * @method static \Illuminate\Database\Eloquent\Builder|RespostaAlternativas whereAlternativaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RespostaAlternativas whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RespostaAlternativas whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RespostaAlternativas whereLinkId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RespostaAlternativas whereOrdem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RespostaAlternativas whereSelecionado($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RespostaAlternativas whereValue($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @mixin \Eloquent
 */
class RespostaAlternativas extends Model
{
    use LogsActivity, HasActivitylogOptions, HasFactory;

    protected static $logName = 'RespostaAlternativas';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

    protected $fillable = [
        'alternativa_id',
        'label',
        'selecionado',
        'value'
    ];

    protected $casts = [
        'alternativa_id' => 'int',
        'label' => 'string',
        'selecionado' => 'boolean',
        'value' => 'int'
    ];
}
