<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class AvaliacaoFeedback extends Model
{
    use HasFactory, TenantTrait, LogsActivity;

    protected static $logFillable = true;
    protected static $logName = 'avaliacoes_feedbacks';
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;

    protected $table = 'avaliacoes_feedbacks';

    protected $fillable = [
        'avaliacao_id',
        'empresa_id',
        'funcionario_id',
        'feedback_id',
        'origem_feedback',
        'principal',
        'avaliador_id',
        'nota_final_total',
        'inicio_feedback',
        'fim_feedback',
        'comentario',
        'status',
        'estado_atual',
        'estado_desejado'
    ];

    protected $casts = [
        'id' => 'int',
        'avaliacao_id' => 'int',
        'empresa_id' => 'int',
        'feedback_id' => 'int',
        'funcionario_id' => 'int',
        'principal' => 'boolean',
        'origem_feedback' => 'string',
        'avaliador_id' => 'int',
        'nota_final_total' => 'int',
        'inicio_feedback' => 'string',
        'fim_feedback' => 'string',
        'comentario' => 'string',
        'status' => 'string',
        'estado_atual' => 'string',
        'estado_desejado' => 'string'
    ];

    public $timestamps = false;

    const ORIGEM_FUNCIONARIO = 'Funcionario';
    const ORIGEM_AVALIADOR = 'Avaliador';

    const LISTA_ORIGEM = [
        self::ORIGEM_FUNCIONARIO,
        self::ORIGEM_AVALIADOR
    ];

    const STATUS_AGUARDANDO = 'Pendente';
    const STATUS_CONCLUIDA = 'Avaliada';
    const STATUS_FINAL = 'Finalizada';

    const LISTA_STATUS = [
        self::STATUS_AGUARDANDO,
        self::STATUS_CONCLUIDA,
        self::STATUS_FINAL
    ];

    public function Avaliador()
    {
        return $this->hasOne(User::class, 'id', 'avaliador_id');
    }

    public function Avaliacao()
    {
        return $this->belongsTo(Avaliacao::class, 'avaliacao_id', 'id');
    }

    public function Funcionario()
    {
        return $this->hasOne(User::class, 'id', 'funcionario_id');
    }

    public function Respostas()
    {
        return $this->hasMany(AvaliacaoResposta::class, 'avaliacao_feedback_id', 'id');
    }

    public function scopeOrigemAvaliador($query)
    {
        return $query->where('origem_feedback', AvaliacaoFeedback::ORIGEM_AVALIADOR);
    }
}
