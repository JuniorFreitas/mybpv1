<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class AvaliacaoAvaliadoresTipos extends Model
{
    use TenantTrait, LogsActivity;

    protected static bool $logFillable = true;
    protected static string $logName = 'avaliacao_avaliadores_tipos';
    protected static bool $logOnlyDirty = true;
    protected static bool $submitEmptyLogs = false;
    public $timestamps = false;

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        $activity->descricao = "";
    }

    protected $table = 'avaliacao_avaliadores_tipos';

    protected $fillable = [
        'empresa_id',
        'label',
        'descricao',
        'ativo',
    ];

    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'label' => 'string',
        'descricao' => 'string',
        'ativo' => 'boolean',
    ];
}
