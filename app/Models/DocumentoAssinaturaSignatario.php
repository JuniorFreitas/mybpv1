<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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
    ];

    protected $casts = [
        'id' => 'int',
        'documento_para_assinatura_id' => 'int',
        'user_id' => 'int',
        'ordem' => 'int',
        'geolocalizacao' => 'array',
        'data_assinatura_utc' => 'datetime',
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
