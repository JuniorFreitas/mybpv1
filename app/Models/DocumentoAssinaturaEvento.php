<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentoAssinaturaEvento extends Model
{
    use LogsActivity, HasActivitylogOptions, HasFactory;

    protected static $logName = 'DocumentoAssinaturaEvento';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

    protected $table = 'documento_assinatura_eventos';

    public $timestamps = true;

    protected $fillable = [
        'documento_para_assinatura_id',
        'evento',
        'payload',
    ];

    protected $casts = [
        'id' => 'int',
        'documento_para_assinatura_id' => 'int',
        'payload' => 'array',
    ];

    const EVENTO_ENVIADO = 'enviado';
    const EVENTO_REENVIADO = 'reenviado';
    const EVENTO_VISUALIZADO = 'visualizado';
    const EVENTO_ASSINADO = 'assinado';
    const EVENTO_RECUSADO = 'recusado';
    const EVENTO_EXPIRADO = 'expirado';
    const EVENTO_CANCELADO = 'cancelado';
    const EVENTO_DOWNLOAD = 'download';
    const EVENTO_EXPORTADO = 'exportado';

    public function documentoParaAssinatura()
    {
        return $this->belongsTo(DocumentoParaAssinatura::class);
    }
}
