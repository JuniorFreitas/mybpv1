<?php

namespace App\Models;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MasterTag\DataHora;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\CartaOferta
 *
 * @property int $id
 * @property string|null $token
 * @property int|null $empresa_id
 * @property int|null $curriculo_id
 * @property int|null $feedback_id
 * @property int|null $vagas_abertas_id
 * @property int|null $vaga_projeto_id
 * @property int|null $arquivo_id
 * @property string $status
 * @property string $local
 * @property array|null $logs
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\Arquivo|null $anexo
 * @property-read \App\Models\Curriculo|null $curriculo
 * @property-read \App\Models\Cliente|null $empresa
 * @property-read \App\Models\FeedbackCurriculo|null $feedback
 * @property-read mixed $log
 * @property-read mixed $ultima_atualizacao
 * @property-read \App\Models\VagasAbertas|null $vagaAberta
 * @property-read \App\Models\VagaProjeto|null $vagaProjeto
 * @method static \Illuminate\Database\Eloquent\Builder|CartaOferta newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CartaOferta newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CartaOferta query()
 * @method static \Illuminate\Database\Eloquent\Builder|CartaOferta whereArquivoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartaOferta whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartaOferta whereCurriculoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartaOferta whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartaOferta whereFeedbackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartaOferta whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartaOferta whereLocal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartaOferta whereLogs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartaOferta whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartaOferta whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartaOferta whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartaOferta whereVagaProjetoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartaOferta whereVagasAbertasId($value)
 * @mixin \Eloquent
 */
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
