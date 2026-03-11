<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\VagaProjetoFeedback
 *
 * @property-read \App\Models\FeedbackCurriculo|null $Feedback
 * @property-read \App\Models\VagaProjeto|null $VagaProjeto
 * @method static \Illuminate\Database\Eloquent\Builder|VagaProjetoFeedback newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VagaProjetoFeedback newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VagaProjetoFeedback query()
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @mixin \Eloquent
 */
class VagaProjetoFeedback extends Model
{
    use LogsActivity, HasActivitylogOptions, HasFactory;

    protected static $logName = 'VagaProjetoFeedback';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

    protected $primaryKey = 'feedback_id';

    protected $fillable = [
        'feedback_id',
        'vaga_projeto_id'
    ];

    protected $casts = [
        'id' => 'int',
        'feedback_id' => 'int',
        'vaga_projeto_id' => 'int'
    ];

    public function VagaProjeto()
    {
        return $this->hasOne(VagaProjeto::class, 'id', 'vaga_projeto_id');
    }

    public function Feedback()
    {
        return $this->hasOne(FeedbackCurriculo::class, 'id', 'feedback_id');
    }
}
