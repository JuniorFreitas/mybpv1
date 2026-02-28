<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentoAssinaturaEvento extends Model
{
    use HasFactory;

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

    public function documentoParaAssinatura()
    {
        return $this->belongsTo(DocumentoParaAssinatura::class);
    }
}
