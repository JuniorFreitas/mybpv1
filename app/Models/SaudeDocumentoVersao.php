<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Versão de um documento SST: arquivo (privado), emissão e vigência.
 */
class SaudeDocumentoVersao extends Model
{
    public const TABELA = 'saude_documentos_versoes';

    protected $table = self::TABELA;

    protected $fillable = [
        'saude_documento_id',
        'versao',
        'arquivo_id',
        'hash_sha256',
        'emissao',
        'vigencia_inicio',
        'vigencia_fim',
        'documento_assinatura_id',
        'enviado_por',
    ];

    protected $casts = [
        'id' => 'int',
        'saude_documento_id' => 'int',
        'versao' => 'int',
        'arquivo_id' => 'int',
        'hash_sha256' => 'string',
        'emissao' => 'date',
        'vigencia_inicio' => 'date',
        'vigencia_fim' => 'date',
        'documento_assinatura_id' => 'int',
        'enviado_por' => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = ['emissao_br', 'vigencia_inicio_br', 'vigencia_fim_br'];

    public function getEmissaoBrAttribute(): ?string
    {
        return $this->emissao?->format('d/m/Y');
    }

    public function getVigenciaInicioBrAttribute(): ?string
    {
        return $this->vigencia_inicio?->format('d/m/Y');
    }

    public function getVigenciaFimBrAttribute(): ?string
    {
        return $this->vigencia_fim?->format('d/m/Y');
    }

    public function Arquivo()
    {
        return $this->belongsTo(Arquivo::class, 'arquivo_id');
    }
}
