<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class CartaOferta extends Model
{
    use HasFactory, LogsActivity, TenantTrait;

    protected static $logFillable = true;
    protected static $logName = 'CartaOferta';
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

    protected $table = 'curriculo_carta_oferta';

    protected $fillable = [
        'token',
        'empresa_id',
        'curriculo_id',
        'feedback_id',
        'vagas_abertas_id',
        'vaga_projeto_id',
        'arquivo_id',
        'status',
        'local',
        'logs',
    ];

    protected $casts = [
        'id' => 'int',
        'token' => 'string',
        'empresa_id' => 'int',
        'curriculo_id' => 'int',
        'feedback_id' => 'int',
        'vagas_abertas_id' => 'int',
        'vaga_projeto_id' => 'int',
        'arquivo_id' => 'int',
        'status' => 'string',
        'local' => 'string',
        'logs' => 'json',
    ];

    protected $appends = [
        'ultima_atualizacao',
    ];

    public function getUltimaAtualizacaoAttribute()
    {
        return (new DataHora($this->updated_at))->dataHoraCompleta();
    }

    const STATUS_PENDENTE_ANEXO = 'Pendente Anexo';
    const STATUS_AGUARDANDO_RH = 'Aguardando RH';
    const STATUS_ACEITO_RH = 'Aceito pelo RH';
    const STATUS_RECUSADO_RH = 'Recusado pelo RH';
    const STATUS_EXPIRADO = 'Expirado';

    const LOCAL_MYBP = 'MYBP';
    const LOCAL_SGI = 'SGI';

    const STATUS = [
        self::STATUS_PENDENTE_ANEXO,
        self::STATUS_AGUARDANDO_RH,
        self::STATUS_ACEITO_RH,
        self::STATUS_RECUSADO_RH,
        self::STATUS_EXPIRADO,
    ];

    public function getLogAttribute($value)
    {
        return json_decode($value, 1);
    }

    public function empresa()
    {
        return $this->hasOne(Cliente::class, 'id', 'empresa_id');
    }

    public function curriculo()
    {
        return $this->hasOne(Curriculo::class, 'id', 'curriculo_id');
    }

    public function feedback()
    {
        return $this->hasOne(FeedbackCurriculo::class, 'id', 'feedback_id');
    }

    public function vagaAberta()
    {
        return $this->hasOne(VagasAbertas::class, 'id', 'vagas_abertas_id');
    }

    public function vagaProjeto()
    {
        return $this->hasOne(VagaProjeto::class, 'id', 'vaga_projeto_id');
    }

    public function anexo()
    {
        return $this->hasOne(Arquivo::class, 'id', 'arquivo_id');
    }

    public static function checklistArquivo($empresa_apelido)
    {
        return env('AWS_URL') . "/public/checklist_{$empresa_apelido}.pdf";
    }

}
