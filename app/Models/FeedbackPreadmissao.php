<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class FeedbackPreadmissao extends Model
{
    use HasFactory, LogsActivity;

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
