<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasActivitylogOptions;
use Spatie\Activitylog\Models\Activity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MasterTag\DataHora;

/**
 * @property int $id
 * @property int|null $curriculo_id
 * @property int|null $feedback_id
 * @property int $empresa_id
 * @property int $user_id
 * @property string $acao
 * @property string $modulo
 * @property string|null $descricao
 * @property array<array-key, mixed>|null $dados_anteriores
 * @property array<array-key, mixed>|null $dados_novos
 * @property array<array-key, mixed>|null $request_completo
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string|null $session_id
 * @property \Illuminate\Support\Carbon $data_acao
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\Curriculo|null $curriculo
 * @property-read \App\Models\User $empresa
 * @property-read \App\Models\FeedbackCurriculo|null $feedback
 * @property-read \App\Models\User $usuario
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecrutamentoHistorico newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecrutamentoHistorico newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecrutamentoHistorico query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecrutamentoHistorico whereAcao($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecrutamentoHistorico whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecrutamentoHistorico whereCurriculoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecrutamentoHistorico whereDadosAnteriores($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecrutamentoHistorico whereDadosNovos($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecrutamentoHistorico whereDataAcao($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecrutamentoHistorico whereDescricao($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecrutamentoHistorico whereEmpresaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecrutamentoHistorico whereFeedbackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecrutamentoHistorico whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecrutamentoHistorico whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecrutamentoHistorico whereModulo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecrutamentoHistorico whereRequestCompleto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecrutamentoHistorico whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecrutamentoHistorico whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecrutamentoHistorico whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RecrutamentoHistorico whereUserId($value)
 * @mixin \Eloquent
 */
class RecrutamentoHistorico extends Model
{
    use LogsActivity, HasActivitylogOptions, HasFactory;

    protected static $logName = 'RecrutamentoHistorico';

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->descricao = '';
    }

    protected $table = 'recrutamento_historicos';

    protected $fillable = [
        'curriculo_id',
        'feedback_id',
        'empresa_id',
        'user_id',
        'acao',
        'modulo',
        'descricao',
        'dados_anteriores',
        'dados_novos',
        'request_completo',
        'ip_address',
        'user_agent',
        'session_id',
        'data_acao'
    ];

    protected $casts = [
        'dados_anteriores' => 'array',
        'dados_novos' => 'array',
        'request_completo' => 'array',
        'data_acao' => 'datetime',
    ];

    // Constantes para ações
    const ACAO_CRIADO = 'criado';
    const ACAO_ATUALIZADO = 'atualizado';
    const ACAO_SELECIONADO = 'selecionado';
    const ACAO_REJEITADO = 'rejeitado';
    const ACAO_TELEFONE_ADICIONADO = 'telefone_adicionado';
    const ACAO_TELEFONE_REMOVIDO = 'telefone_removido';
    const ACAO_TELEFONE_ATUALIZADO = 'telefone_atualizado';
    const ACAO_DOCUMENTO_ADICIONADO = 'documento_adicionado';
    const ACAO_DOCUMENTO_REMOVIDO = 'documento_removido';
    const ACAO_FEEDBACK_CRIADO = 'feedback_criado';
    const ACAO_FEEDBACK_ATUALIZADO = 'feedback_atualizado';
    const ACAO_EMAIL_ENVIADO = 'email_enviado';
    const ACAO_WHATSAPP_ENVIADO = 'whatsapp_enviado';
    const ACAO_MARCADO_LIDO = 'marcado_lido';
    const ACAO_EXPORTADO = 'exportado';

    // Constantes para módulos
    const MODULO_CURRICULO = 'curriculo';
    const MODULO_FEEDBACK = 'feedback';
    const MODULO_TELEFONE = 'telefone';
    const MODULO_DOCUMENTO = 'documento';
    const MODULO_EMAIL = 'email';
    const MODULO_WHATSAPP = 'whatsapp';
    const MODULO_EXPORT = 'export';

    /**
     * Relacionamento com Curriculo
     */
    public function curriculo(): BelongsTo
    {
        return $this->belongsTo(Curriculo::class, 'curriculo_id');
    }

    /**
     * Relacionamento com FeedbackCurriculo
     */
    public function feedback(): BelongsTo
    {
        return $this->belongsTo(FeedbackCurriculo::class, 'feedback_id');
    }

    /**
     * Relacionamento com User (empresa)
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'empresa_id');
    }

    /**
     * Relacionamento com User (usuário que fez a ação)
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Registra uma ação no histórico.
     * curriculoId pode ser null para ações sem currículo específico (ex.: exportação em massa).
     */
    public static function registrar(
        ?int $curriculoId,
        string $acao,
        string $modulo,
        ?int $feedbackId = null,
        ?string $descricao = null,
        ?array $dadosAnteriores = null,
        ?array $dadosNovos = null,
        ?array $requestCompleto = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        ?string $sessionId = null
    ): self {
        return self::create([
            'curriculo_id' => $curriculoId,
            'feedback_id' => $feedbackId,
            'empresa_id' => auth()->user()->empresa_id,
            'user_id' => auth()->id(),
            'acao' => $acao,
            'modulo' => $modulo,
            'descricao' => $descricao,
            'dados_anteriores' => $dadosAnteriores,
            'dados_novos' => $dadosNovos,
            'request_completo' => $requestCompleto,
            'ip_address' => $ipAddress ?? request()->ip(),
            'user_agent' => $userAgent ?? request()->userAgent(),
            'session_id' => $sessionId ?? session()->getId(),
            'data_acao' => (new DataHora())->dataHoraInsert()
        ]);
    }

    /**
     * Busca histórico por currículo
     */
    public static function porCurriculo(int $curriculoId, ?int $limit = null)
    {
        $query = self::where('curriculo_id', $curriculoId)
            ->with(['usuario', 'feedback'])
            ->orderBy('data_acao', 'desc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Busca histórico por empresa
     */
    public static function porEmpresa(int $empresaId, ?int $limit = null)
    {
        $query = self::where('empresa_id', $empresaId)
            ->with(['curriculo', 'usuario', 'feedback'])
            ->orderBy('data_acao', 'desc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Busca histórico por ação
     */
    public static function porAcao(string $acao, ?int $limit = null)
    {
        $query = self::where('acao', $acao)
            ->with(['curriculo', 'usuario', 'feedback'])
            ->orderBy('data_acao', 'desc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Busca histórico por módulo
     */
    public static function porModulo(string $modulo, ?int $limit = null)
    {
        $query = self::where('modulo', $modulo)
            ->with(['curriculo', 'usuario', 'feedback'])
            ->orderBy('data_acao', 'desc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Gera descrição automática baseada na ação
     */
    public function gerarDescricao(): string
    {
        $usuario = $this->usuario->nome ?? 'Usuário desconhecido';
        $curriculo = $this->curriculo_id !== null
            ? ($this->curriculo?->nome ?? 'Currículo #' . $this->curriculo_id)
            : null;

        if ($curriculo === null) {
            return $this->descricao ?? "Exportação em massa realizada por {$usuario}";
        }

        switch ($this->acao) {
            case self::ACAO_CRIADO:
                return "Currículo de {$curriculo} foi criado por {$usuario}";
            case self::ACAO_ATUALIZADO:
                return "Dados do currículo de {$curriculo} foram atualizados por {$usuario}";
            case self::ACAO_SELECIONADO:
                return "Candidato {$curriculo} foi selecionado por {$usuario}";
            case self::ACAO_REJEITADO:
                return "Candidato {$curriculo} foi rejeitado por {$usuario}";
            case self::ACAO_TELEFONE_ADICIONADO:
                return "Telefone foi adicionado ao currículo de {$curriculo} por {$usuario}";
            case self::ACAO_TELEFONE_REMOVIDO:
                return "Telefone foi removido do currículo de {$curriculo} por {$usuario}";
            case self::ACAO_TELEFONE_ATUALIZADO:
                return "Telefone foi atualizado no currículo de {$curriculo} por {$usuario}";
            case self::ACAO_EMAIL_ENVIADO:
                return "Email foi enviado para {$curriculo} por {$usuario}";
            case self::ACAO_WHATSAPP_ENVIADO:
                return "WhatsApp foi enviado para {$curriculo} por {$usuario}";
            case self::ACAO_MARCADO_LIDO:
                return "Currículo de {$curriculo} foi marcado como lido por {$usuario}";
            case self::ACAO_EXPORTADO:
                return "Dados de {$curriculo} foram exportados por {$usuario}";
            default:
                return "Ação '{$this->acao}' realizada em {$curriculo} por {$usuario}";
        }
    }
}
