<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\MotivoRescisaoCurriculo
 *
 * @property int $motivo_id
 * @property int|null $feedback_id
 * @property string|null $outro
 * @method static \Illuminate\Database\Eloquent\Builder|MotivoRescisaoCurriculo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MotivoRescisaoCurriculo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MotivoRescisaoCurriculo query()
 * @method static \Illuminate\Database\Eloquent\Builder|MotivoRescisaoCurriculo whereFeedbackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MotivoRescisaoCurriculo whereMotivoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MotivoRescisaoCurriculo whereOutro($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @mixin \Eloquent
 */
class MotivoRescisaoCurriculo extends Model
{
    use LogsActivity, HasActivitylogOptions, HasFactory;

    protected static $logName = 'MotivoRescisaoCurriculo';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

    protected $table = 'motivo_rescisao_curriculo';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'motivo_id',
        'feedback_id',
        'outro',
    ];

    protected $casts = [
        'id' => 'int',
        'motivo_id' => 'int',
        'feedback_id' => 'int',
        'outro' => 'string',
    ];
}
