<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;

/**
 * App\Models\FeedbackPreadmissao
 *
 * @property int $id
 * @property int $feedback_id
 * @property int $user_finalizou_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\FeedbackCurriculo $Feedback
 * @property-read \App\Models\User $UserFinalizou
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @method static \Illuminate\Database\Eloquent\Builder|FeedbackPreadmissao newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FeedbackPreadmissao newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FeedbackPreadmissao query()
 * @method static \Illuminate\Database\Eloquent\Builder|FeedbackPreadmissao whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedbackPreadmissao whereFeedbackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedbackPreadmissao whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedbackPreadmissao whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeedbackPreadmissao whereUserFinalizouId($value)
 * @mixin \Eloquent
 */
class FeedbackPreadmissao extends Model
{
    use HasFactory, LogsActivity, HasActivitylogOptions;

    protected static $logFillable = true;
    protected static $logName = 'feedback_preadmissao';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;
    protected $table = 'feedback_preadmissao';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = "";
    }

    protected $fillable = [
        'feedback_id',
        'user_finalizou_id',
    ];

    protected $casts = [
        'id' => 'int',
        'feedback_id' => 'int',
        'user_finalizou_id' => 'int',
    ];

    public function Feedback()
    {
        return $this->belongsTo(FeedbackCurriculo::class, 'feedback_id');
    }

    public function UserFinalizou()
    {
        return $this->belongsTo(User::class, 'user_finalizou_id');
    }
}
