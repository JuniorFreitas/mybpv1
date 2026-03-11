<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $documento_para_assinatura_id
 * @property int|null $user_id
 * @property string $email
 * @property string $nome
 * @property string|null $cpf
 * @property int $ordem
 * @property string $token
 * @property string $status
 * @property string|null $ip
 * @property string|null $user_agent
 * @property \Illuminate\Support\Carbon|null $data_assinatura_utc
 * @property array<array-key, mixed>|null $geolocalizacao
 * @property string|null $hash_evidencia
 * @property string|null $recusa_motivo
 * @property bool $consentimento_assinatura
 * @property \Illuminate\Support\Carbon|null $consentimento_em
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\DocumentoParaAssinatura $documentoParaAssinatura
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentoAssinaturaSignatario newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentoAssinaturaSignatario newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentoAssinaturaSignatario query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentoAssinaturaSignatario whereConsentimentoAssinatura($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentoAssinaturaSignatario whereConsentimentoEm($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentoAssinaturaSignatario whereCpf($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentoAssinaturaSignatario whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentoAssinaturaSignatario whereDataAssinaturaUtc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentoAssinaturaSignatario whereDocumentoParaAssinaturaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentoAssinaturaSignatario whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentoAssinaturaSignatario whereGeolocalizacao($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentoAssinaturaSignatario whereHashEvidencia($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentoAssinaturaSignatario whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentoAssinaturaSignatario whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentoAssinaturaSignatario whereNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentoAssinaturaSignatario whereOrdem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentoAssinaturaSignatario whereRecusaMotivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentoAssinaturaSignatario whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentoAssinaturaSignatario whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentoAssinaturaSignatario whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentoAssinaturaSignatario whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentoAssinaturaSignatario whereUserId($value)
 * @mixin \Eloquent
 */
class DocumentoAssinaturaSignatario extends Model
{
    use LogsActivity, HasActivitylogOptions, HasFactory;

    protected static $logName = 'DocumentoAssinaturaSignatario';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

    protected $table = 'documento_assinatura_signatarios';

    protected $fillable = [
        'documento_para_assinatura_id',
        'user_id',
        'email',
        'nome',
        'cpf',
        'ordem',
        'token',
        'status',
        'ip',
        'user_agent',
        'data_assinatura_utc',
        'geolocalizacao',
        'hash_evidencia',
        'recusa_motivo',
        'consentimento_assinatura',
        'consentimento_em',
    ];

    protected $casts = [
        'id' => 'int',
        'documento_para_assinatura_id' => 'int',
        'user_id' => 'int',
        'ordem' => 'int',
        'geolocalizacao' => 'array',
        'data_assinatura_utc' => 'datetime',
        'consentimento_assinatura' => 'boolean',
        'consentimento_em' => 'datetime',
    ];

    const STATUS_PENDENTE = 'pendente';
    const STATUS_ASSINADO = 'assinado';
    const STATUS_RECUSADO = 'recusado';
    const STATUS_EXPIRADO = 'expirado';

    public static function gerarToken(): string
    {
        return Str::random(64);
    }

    public function documentoParaAssinatura()
    {
        return $this->belongsTo(DocumentoParaAssinatura::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
