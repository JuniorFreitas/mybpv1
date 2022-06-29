<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class Afastamento extends Model
{
    use HasFactory, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'afastamento';
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

    protected $fillable = [
        'feedback_id',
        'cadastrado_id',
        'motivo',
        'data_inicio',
        'data_fim',
        'observacao',
    ];

    protected $casts = [
        'feedback_id' => 'int',
        'cadastrado_id' =>'int',
        'motivo' => 'text',
        'data_inicio' => 'string',
        'data_fim' => 'string',
        'observacao' =>'string',
    ];

    public function Anexos()
    {
        return $this->belongsToMany(Arquivo::class, 'afastamento_anexos', 'afastamento_id', 'arquivo_id');
    }

    public function Feedback()
    {
        return $this->hasOne(FeedbackCurriculo::class, 'id', 'feedback_id');
    }

}
