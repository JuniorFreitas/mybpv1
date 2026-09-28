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
 * @property int|null $empresa_id
 * @property string $label
 * @property bool $ativo
 * @property int|null $formulario_encaminhamento_id
 * @property int|null $formulario_resultado_id
 * @property int $ordem
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
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

    protected $table = 'exame_tipos';

    protected $fillable = [
        'empresa_id',
        'label',
        'ativo',
        'formulario_encaminhamento_id',
        'formulario_resultado_id',
        'ordem',
    ];

    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'label' => 'string',
        'ativo' => 'boolean',
        'formulario_encaminhamento_id' => 'int',
        'formulario_resultado_id' => 'int',
        'ordem' => 'int',
    ];

    public function FormularioEncaminhamento()
    {
        return $this->belongsTo(Formulario::class, 'formulario_encaminhamento_id');
    }

    public function FormularioResultado()
    {
        return $this->belongsTo(Formulario::class, 'formulario_resultado_id');
    }

    public function Empresa()
    {
        return $this->belongsTo(Cliente::class, 'empresa_id');
    }

    public function scopeVisivelParaEmpresa($query, ?int $empresaId)
    {
        return $query->where(function ($q) use ($empresaId) {
            $q->whereNull('empresa_id');
            if ($empresaId) {
                $q->orWhere('empresa_id', $empresaId);
            }
        });
    }
}
