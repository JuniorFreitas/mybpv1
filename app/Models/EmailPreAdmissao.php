<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class EmailPreAdmissao extends Model
{
    use HasFactory, TenantTrait, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'curriculo';
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

    protected $table = 'emails_pre_admissao';

    protected $fillable = [
        'empresa_id',
        'curriculo_id',
        'quem_enviou_id',
        'observacao',
        'email_atual',
        'email_padrao'
    ];

    protected $cast = [
        'empresa_id' => 'int',
        'curriculo_id' => 'int',
        'quem_enviou_id' => 'int',
        'observacao' => 'string',
        'email_atual' => 'boolean',
        'email_padrao' => 'boolean'

    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->quem_enviou_id = auth()->check() ? auth()->id() : $model->quem_enviou_id;
        });
    }
}
