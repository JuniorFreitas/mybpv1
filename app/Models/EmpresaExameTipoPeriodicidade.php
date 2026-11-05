<?php

namespace App\Models;

use App\Models\Concerns\HasActivitylogOptions;
use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class EmpresaExameTipoPeriodicidade extends Model
{
    use LogsActivity, HasActivitylogOptions, TenantTrait;

    public const UNIDADE_ANOS = 'anos';

    public const UNIDADE_DIAS = 'dias';

    public const TABELA = 'empresas_exame_tipos_periodicidades';

    protected $table = self::TABELA;

    protected static $logName = 'EmpresaExameTipoPeriodicidade';

    protected static $logFillable = true;

    protected static $logOnlyDirty = true;

    protected static $submitEmptyLogs = false;

    protected $fillable = [
        'empresa_id',
        'exame_tipo_id',
        'unidade',
        'valor',
    ];

    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'exame_tipo_id' => 'int',
        'unidade' => 'string',
        'valor' => 'int',
    ];

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        $activity->descricao = '';
    }

    public function ExameTipo()
    {
        return $this->belongsTo(ExameTipo::class, 'exame_tipo_id', 'id');
    }
}
