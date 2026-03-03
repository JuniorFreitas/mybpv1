<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use App\Tenant\Traits\TenantTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentoParaAssinatura extends Model
{
    use LogsActivity, HasActivitylogOptions, HasFactory, TenantTrait;

    protected static $logName = 'DocumentoParaAssinatura';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

    protected $table = 'documento_para_assinatura';

    protected $fillable = [
        'empresa_id',
        'token',
        'tipo_documento',
        'documentable_type',
        'documentable_id',
        'arquivo_id',
        'arquivo_assinado_id',
        'hash_sha256',
        'status',
        'data_expiracao',
        'solicitante_id',
        'ordem_assinatura',
        'consentimento_ultimo_em',
        'consentimento_ultimo_signatario_id',
    ];

    protected $casts = [
        'id' => 'int',
        'empresa_id' => 'int',
        'arquivo_id' => 'int',
        'arquivo_assinado_id' => 'int',
        'solicitante_id' => 'int',
        'documentable_id' => 'int',
        'data_expiracao' => 'datetime',
        'consentimento_ultimo_em' => 'datetime',
        'consentimento_ultimo_signatario_id' => 'int',
    ];

    const STATUS_RASCUNHO = 'rascunho';
    const STATUS_ENVIADO = 'enviado';
    const STATUS_EM_ASSINATURA = 'em_assinatura';
    const STATUS_CONCLUIDO = 'concluido';
    const STATUS_EXPIRADO = 'expirado';
    const STATUS_CANCELADO = 'cancelado';

    const ORDEM_SEQUENCIAL = 'sequencial';
    const ORDEM_PARALELO = 'paralelo';

    const TIPOS_DOCUMENTO = [
        'contrato_legal',
        'contrato_trabalho',
        'carta_oferta',
        'termo_demissao',
        'ficha_encaminhamento',
        'termo_confidencialidade',
        'opcao_vale_transporte',
        'acordo_compensacao_horas',
        'termo_salario_familia',
        'declaracao_dependentes_ir',
        'medida_administrativa',
        'documento_demissao',
    ];

    /** Labels para exibição (e-mail, auditoria, etc.). */
    public static function labelTipoDocumento(string $tipo): string
    {
        $labels = [
            'contrato_legal' => 'Contrato (Documentos Legais)',
            'contrato_trabalho' => 'Contrato de Trabalho',
            'carta_oferta' => 'Carta Oferta',
            'termo_demissao' => 'Termo de Demissão',
            'ficha_encaminhamento' => 'Ficha de Encaminhamento',
            'termo_confidencialidade' => 'Termo de Confidencialidade',
            'opcao_vale_transporte' => 'Opção Vale Transporte',
            'acordo_compensacao_horas' => 'Acordo de Compensação de Horas',
            'termo_salario_familia' => 'Termo Salário Família',
            'declaracao_dependentes_ir' => 'Declaração Dependentes IR',
            'medida_administrativa' => 'Medida Administrativa',
            'documento_demissao' => 'Documento de Demissão (Aviso Prévio)',
        ];
        return $labels[$tipo] ?? $tipo;
    }

    public function empresa()
    {
        return $this->belongsTo(Cliente::class, 'empresa_id');
    }

    public function documentable()
    {
        return $this->morphTo();
    }

    public function arquivo()
    {
        return $this->belongsTo(Arquivo::class);
    }

    /** PDF com marca d'água "ASSINADO DIGITALMENTE" (gerado ao concluir). */
    public function arquivoAssinado()
    {
        return $this->belongsTo(Arquivo::class, 'arquivo_assinado_id');
    }

    public function solicitante()
    {
        return $this->belongsTo(User::class, 'solicitante_id');
    }

    public function signatarios()
    {
        return $this->hasMany(DocumentoAssinaturaSignatario::class, 'documento_para_assinatura_id')->orderBy('ordem');
    }

    public function eventos()
    {
        return $this->hasMany(DocumentoAssinaturaEvento::class, 'documento_para_assinatura_id')->orderBy('created_at');
    }

    public function scopePorIdOuToken($query, $idOrToken)
    {
        if ($idOrToken === null || $idOrToken === '') {
            return $query->whereRaw('1 = 0');
        }
        if (is_numeric($idOrToken)) {
            return $query->where('id', (int) $idOrToken);
        }
        return $query->where('token', $idOrToken);
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function (self $model) {
            if (empty($model->token)) {
                $model->token = \Illuminate\Support\Str::random(32);
            }
        });
    }
}
