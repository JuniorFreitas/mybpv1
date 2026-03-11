<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ExameTipo
 *
 * @property int $id
 * @property string $label
 * @property bool $ativo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ExameTipo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExameTipo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExameTipo query()
 * @method static \Illuminate\Database\Eloquent\Builder|ExameTipo whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameTipo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameTipo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameTipo whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExameTipo whereUpdatedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @mixin \Eloquent
 */
class ExameTipo extends Model
{
    use LogsActivity, HasActivitylogOptions, HasFactory;

    protected static $logName = 'ExameTipo';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

    protected $fillable = [
        'label',
        'ativo'
    ];
    protected $casts = [
        'id' => 'int',
        'label' => 'string',
        'ativo' => 'boolean'
    ];
}
