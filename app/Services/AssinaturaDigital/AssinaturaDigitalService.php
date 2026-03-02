<?php

namespace App\Services\AssinaturaDigital;

use App\Jobs\AssinaturaDigital\JobEnvioCodigoVerificacaoAssinatura;
use App\Jobs\AssinaturaDigital\JobFinalizarDocumentoAssinado;
use App\Jobs\AssinaturaDigital\JobEnvioDocumentoAssinado;
use App\Jobs\AssinaturaDigital\JobEnvioEmailAssinatura;
use App\Models\Arquivo;
use App\Models\CartaOferta;
use App\Models\Cliente;
use App\Models\DocumentoAssinaturaEvento;
use App\Models\DocumentoAssinaturaSignatario;
use App\Models\DocumentoParaAssinatura;
use App\Models\Sistema;
use App\Models\User;
use MasterTag\DataHora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AssinaturaDigitalService
{
    protected const CODIGO_VERIFICACAO_TTL_MINUTOS = 10;
    protected const CODIGO_VERIFICACAO_MAX_TENTATIVAS = 5;
    protected const CODIGO_VERIFICACAO_BLOQUEIO_MINUTOS = 15;
    protected const CODIGO_REENVIO_COOLDOWN_SEGUNDOS = 60;

    /**
     * Cria um envio para assinatura: persiste o PDF, calcula hash, cria documento e signatários.
     *
     * @param int $empresaId (clientes.id)
     * @param string $tipoDocumento
     * @param string $documentableType (classe do model)
     * @param int $documentableId
     * @param int|null $solicitanteId (users.id)
     * @param array $signatarios [ ['user_id' => ?|null, 'email' => '', 'nome' => '', 'cpf' => ?|null], ... ]
     * @param string $ordemAssinatura DocumentoParaAssinatura::ORDEM_SEQUENCIAL|ORDEM_PARALELO
     * @param string $pdfContent Conteúdo binário do PDF
     * @param string $nomeArquivo Nome sugerido para o arquivo (ex: "contrato_joao.pdf")
     * @param \DateTimeInterface|null $dataExpiracao
     * @return DocumentoParaAssinatura
     */
    public function criarEnvio(
        int $empresaId,
        string $tipoDocumento,
        string $documentableType,
        int $documentableId,
        ?int $solicitanteId,
        array $signatarios,
        string $ordemAssinatura,
        string $pdfContent,
        string $nomeArquivo = 'documento.pdf',
        ?\DateTimeInterface $dataExpiracao = null
    ): DocumentoParaAssinatura {
        app(AssinaturaCotaService::class)->validarDisponibilidadeOrFail($empresaId);

        $disco = Arquivo::DISCO_DOCUMENTO_ASSINATURA;
        Log::info('AssinaturaDigitalService::criarEnvio iniciado', ['tipo' => $tipoDocumento, 'empresa_id' => $empresaId]);

        $hashSha256 = hash('sha256', $pdfContent);
        $safeName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', pathinfo($nomeArquivo, PATHINFO_FILENAME)) . '.pdf';
        $path = date('Y/m/d/') . uniqid() . '_' . $safeName;

        try {
            Storage::disk($disco)->put($path, $pdfContent);
        } catch (\Throwable $e) {
            Log::error('AssinaturaDigitalService: falha ao gravar PDF no disco', ['disco' => $disco, 'path' => $path, 'erro' => $e->getMessage()]);
            throw $e;
        }
        $bytes = strlen($pdfContent);

        $arquivo = Arquivo::create([
            'quem_enviou' => $solicitanteId,
            'nome' => $safeName,
            'imagem' => false,
            'layout' => null,
            'extensao' => '.pdf',
            'file' => $path,
            'thumb' => null,
            'bytes' => $bytes,
            'temporario' => false,
            'chave' => null,
            'disco' => $disco,
        ]);

        $doc = DB::transaction(function () use (
            $empresaId,
            $tipoDocumento,
            $documentableType,
            $documentableId,
            $solicitanteId,
            $signatarios,
            $ordemAssinatura,
            $hashSha256,
            $arquivo,
            $dataExpiracao
        ) {
            $doc = DocumentoParaAssinatura::create([
                'empresa_id' => $empresaId,
                'tipo_documento' => $tipoDocumento,
                'documentable_type' => $documentableType,
                'documentable_id' => $documentableId,
                'arquivo_id' => $arquivo->id,
                'hash_sha256' => $hashSha256,
                'status' => DocumentoParaAssinatura::STATUS_EM_ASSINATURA,
                'data_expiracao' => $dataExpiracao,
                'solicitante_id' => $solicitanteId,
                'ordem_assinatura' => $ordemAssinatura,
            ]);

            $ordem = 0;
            foreach ($signatarios as $s) {
                $ordem++;
                $email = $s['email'] ?? null;
                $nome = $s['nome'] ?? '';
                $userId = $s['user_id'] ?? null;
                $cpf = $s['cpf'] ?? null;
                if ($userId && !$email) {
                    $user = User::find($userId);
                    if ($user) {
                        $email = $user->login ?? $user->email ?? $email;
                        $nome = $nome ?: $user->nome;
                        if ($cpf === null && !empty($user->cpf)) {
                            $cpf = $user->cpf;
                        }
                    }
                }
                if (!$email) {
                    Log::warning('AssinaturaDigitalService: signatário sem e-mail ignorado', ['nome' => $nome, 'ordem' => $ordem]);
                    continue;
                }
                DocumentoAssinaturaSignatario::create([
                    'documento_para_assinatura_id' => $doc->id,
                    'user_id' => $userId,
                    'email' => $email,
                    'nome' => $nome,
                    'cpf' => $cpf,
                    'ordem' => $ordem,
                    'token' => DocumentoAssinaturaSignatario::gerarToken(),
                    'status' => DocumentoAssinaturaSignatario::STATUS_PENDENTE,
                ]);
            }

            $doc->load('signatarios');
            if ($doc->signatarios->isEmpty()) {
                Log::error('AssinaturaDigitalService: nenhum signatário criado (todos sem e-mail?)');
                throw new \InvalidArgumentException('Nenhum signatário com e-mail válido. Verifique a lista de signatários.');
            }
            $solicitante = User::find($solicitanteId);
            $this->registrarEvento($doc->id, DocumentoAssinaturaEvento::EVENTO_ENVIADO, [
                'solicitante_id' => $solicitanteId,
                'nome' => $solicitante ? ($solicitante->nome ?? $solicitante->name ?? 'Sistema') : 'Sistema',
                'signatarios_count' => $doc->signatarios->count(),
            ]);

            return $doc->fresh(['signatarios', 'arquivo']);
        });

        // Envia e-mail de forma assíncrona (background / queue worker).
        Log::info('AssinaturaDigital: criando envio e enfileirando e-mail', ['documento_id' => $doc->id, 'tipo' => $tipoDocumento, 'signatarios' => $doc->signatarios->pluck('email')->toArray()]);
        JobEnvioEmailAssinatura::dispatch($doc->id);
        app(AssinaturaCotaService::class)->verificarAlertas($empresaId);

        return $doc;
    }

    /**
     * Resolve a empresa (Cliente) pelo apelido da URL. Usa withoutGlobalScopes para funcionar sem login.
     */
    public function buscarEmpresaPorApelido(string $apelido): ?Cliente
    {
        return Cliente::withoutGlobalScopes()
            ->where('apelido', $apelido)
            ->first();
    }

    /**
     * Busca signatário por token (para página pública). Não aplica scope de empresa.
     * Carrega empresa com Cliente::withoutGlobalScopes() para funcionar sem usuário logado.
     */
    public function buscarSignatarioPorToken(string $token): ?DocumentoAssinaturaSignatario
    {
        $signatario = DocumentoAssinaturaSignatario::with([
            'documentoParaAssinatura.arquivo',
        ])->where('token', $token)->first();

        if (!$signatario || !$signatario->documentoParaAssinatura) {
            return $signatario;
        }

        $empresa = Cliente::withoutGlobalScopes()->find($signatario->documentoParaAssinatura->empresa_id);
        $signatario->documentoParaAssinatura->setRelation('empresa', $empresa);

        return $signatario;
    }

    /**
     * Valida o link público: apelido (empresa) + token (signatário).
     * Resolve a empresa pelo apelido (campo em clientes) e confere se o token pertence a essa empresa.
     */
    public function validarTokenParaEmpresa(string $token, string $apelido): ?DocumentoAssinaturaSignatario
    {
        $empresa = $this->buscarEmpresaPorApelido($apelido);
        if (!$empresa) {
            return null;
        }

        $signatario = DocumentoAssinaturaSignatario::with(['documentoParaAssinatura.arquivo'])
            ->where('token', $token)
            ->whereHas('documentoParaAssinatura', fn ($q) => $q->where('empresa_id', $empresa->id))
            ->first();

        if (!$signatario) {
            return null;
        }

        $signatario->documentoParaAssinatura->setRelation('empresa', $empresa);
        return $signatario;
    }

    /**
     * Valida CPF informado para o signatário do token.
     * Se o CPF do signatário ainda não estiver cadastrado, salva o CPF informado.
     */
    public function validarCpfSignatario(DocumentoAssinaturaSignatario $signatario, string $cpfInformado): bool
    {
        $cpfLimpo = preg_replace('/\D/', '', $cpfInformado);
        if (strlen($cpfLimpo) !== 11) {
            return false;
        }
        if (Sistema::validaCPF($cpfLimpo) !== true) {
            return false;
        }

        $cpfFormatado = substr($cpfLimpo, 0, 3) . '.' . substr($cpfLimpo, 3, 3) . '.' . substr($cpfLimpo, 6, 3) . '-' . substr($cpfLimpo, 9, 2);
        $cpfAtualLimpo = preg_replace('/\D/', '', (string) $signatario->cpf);

        if ($cpfAtualLimpo !== '') {
            return hash_equals($cpfAtualLimpo, $cpfLimpo);
        }

        $signatario->update(['cpf' => $cpfFormatado]);
        return true;
    }

    /**
     * Gera e envia código alfanumérico de verificação por e-mail.
     */
    public function enviarCodigoVerificacao(DocumentoAssinaturaSignatario $signatario, ?Cliente $empresa = null): void
    {
        $codigo = Str::upper(Str::random(8));
        Cache::put($this->chaveCacheCodigoVerificacao($signatario->id), [
            'hash' => hash('sha256', $codigo),
        ], now()->addMinutes(self::CODIGO_VERIFICACAO_TTL_MINUTOS));
        Cache::put($this->chaveCacheCooldownCodigo($signatario->id), [
            'reenviar_apos' => now()->addSeconds(self::CODIGO_REENVIO_COOLDOWN_SEGUNDOS)->timestamp,
        ], now()->addMinutes(30));

        $empresaNome = $empresa ? ($empresa->razao_social ?? $empresa->nome_fantasia ?? '') : '';
        $empresaId = $empresa ? (int) $empresa->id : null;
        JobEnvioCodigoVerificacaoAssinatura::dispatch(
            $signatario->id,
            $codigo,
            self::CODIGO_VERIFICACAO_TTL_MINUTOS,
            $empresaNome,
            $empresaId
        );
    }

    /**
     * Valida código de verificação enviado ao e-mail do signatário.
     */
    public function validarCodigoVerificacao(DocumentoAssinaturaSignatario $signatario, string $codigoInformado): bool
    {
        $registro = Cache::get($this->chaveCacheCodigoVerificacao($signatario->id));
        if (!is_array($registro) || empty($registro['hash'])) {
            return false;
        }

        $codigoNormalizado = strtoupper(trim($codigoInformado));
        return hash_equals((string) $registro['hash'], hash('sha256', $codigoNormalizado));
    }

    public function invalidarCodigoVerificacao(DocumentoAssinaturaSignatario $signatario): void
    {
        Cache::forget($this->chaveCacheCodigoVerificacao($signatario->id));
    }

    /**
     * Verifica se o signatário pode reenviar o código agora.
     *
     * @return array{pode_enviar: bool, segundos_restantes: int}
     */
    public function podeReenviarCodigoVerificacao(DocumentoAssinaturaSignatario $signatario): array
    {
        $dados = Cache::get($this->chaveCacheCooldownCodigo($signatario->id));
        if (!is_array($dados) || empty($dados['reenviar_apos'])) {
            return ['pode_enviar' => true, 'segundos_restantes' => 0];
        }

        $restante = (int) $dados['reenviar_apos'] - now()->timestamp;
        if ($restante <= 0) {
            return ['pode_enviar' => true, 'segundos_restantes' => 0];
        }

        return ['pode_enviar' => false, 'segundos_restantes' => $restante];
    }

    /**
     * Retorna status de bloqueio por tentativas inválidas.
     *
     * @return array{bloqueado: bool, segundos_restantes: int, tentativas_restantes: int}
     */
    public function statusTentativasCodigoVerificacao(DocumentoAssinaturaSignatario $signatario): array
    {
        $dados = Cache::get($this->chaveCacheTentativasCodigo($signatario->id));
        if (!is_array($dados)) {
            return [
                'bloqueado' => false,
                'segundos_restantes' => 0,
                'tentativas_restantes' => self::CODIGO_VERIFICACAO_MAX_TENTATIVAS,
            ];
        }

        $bloqueadoAte = (int) ($dados['bloqueado_ate'] ?? 0);
        $count = (int) ($dados['count'] ?? 0);
        $segundosRestantes = max(0, $bloqueadoAte - now()->timestamp);
        $bloqueado = $segundosRestantes > 0 && $count >= self::CODIGO_VERIFICACAO_MAX_TENTATIVAS;
        $tentativasRestantes = max(0, self::CODIGO_VERIFICACAO_MAX_TENTATIVAS - $count);

        if (!$bloqueado && $segundosRestantes <= 0 && $count >= self::CODIGO_VERIFICACAO_MAX_TENTATIVAS) {
            $this->limparTentativasCodigoVerificacao($signatario);
            return [
                'bloqueado' => false,
                'segundos_restantes' => 0,
                'tentativas_restantes' => self::CODIGO_VERIFICACAO_MAX_TENTATIVAS,
            ];
        }

        return [
            'bloqueado' => $bloqueado,
            'segundos_restantes' => $segundosRestantes,
            'tentativas_restantes' => $tentativasRestantes,
        ];
    }

    /**
     * Registra tentativa inválida e retorna status atualizado.
     *
     * @return array{bloqueado: bool, segundos_restantes: int, tentativas_restantes: int}
     */
    public function registrarFalhaCodigoVerificacao(DocumentoAssinaturaSignatario $signatario): array
    {
        $statusAtual = $this->statusTentativasCodigoVerificacao($signatario);
        if ($statusAtual['bloqueado']) {
            return $statusAtual;
        }

        $dados = Cache::get($this->chaveCacheTentativasCodigo($signatario->id));
        $count = is_array($dados) ? (int) ($dados['count'] ?? 0) : 0;
        $count++;

        $bloqueadoAte = 0;
        if ($count >= self::CODIGO_VERIFICACAO_MAX_TENTATIVAS) {
            $bloqueadoAte = now()->addMinutes(self::CODIGO_VERIFICACAO_BLOQUEIO_MINUTOS)->timestamp;
        }

        Cache::put($this->chaveCacheTentativasCodigo($signatario->id), [
            'count' => $count,
            'bloqueado_ate' => $bloqueadoAte,
        ], now()->addMinutes(self::CODIGO_VERIFICACAO_BLOQUEIO_MINUTOS + 5));

        return $this->statusTentativasCodigoVerificacao($signatario);
    }

    public function limparTentativasCodigoVerificacao(DocumentoAssinaturaSignatario $signatario): void
    {
        Cache::forget($this->chaveCacheTentativasCodigo($signatario->id));
    }

    /**
     * Registra evento de auditoria.
     */
    public function registrarEvento(int $documentoParaAssinaturaId, string $evento, array $payload = []): DocumentoAssinaturaEvento
    {
        return DocumentoAssinaturaEvento::create([
            'documento_para_assinatura_id' => $documentoParaAssinaturaId,
            'evento' => $evento,
            'payload' => $payload,
        ]);
    }

    /**
     * Assina o documento (signatário confirma pelo token). Persiste evidências para base legal.
     *
     * @param string $token
     * @param Request $request Para capturar IP, user_agent e opcionalmente cpf
     * @param string|null $cpfInformado CPF informado na página (quando não vinha do cadastro)
     * @return array ['success' => bool, 'message' => ?, 'documento' => ?]
     */
    public function assinar(string $token, Request $request, ?string $cpfInformado = null): array
    {
        $signatario = $this->buscarSignatarioPorToken($token);
        if (!$signatario) {
            return ['success' => false, 'message' => 'Link inválido ou expirado.'];
        }
        if ($signatario->status !== DocumentoAssinaturaSignatario::STATUS_PENDENTE) {
            return ['success' => false, 'message' => 'Este documento já foi assinado ou recusado.'];
        }

        $doc = $signatario->documentoParaAssinatura;
        if ($doc->status === DocumentoParaAssinatura::STATUS_CONCLUIDO || $doc->status === DocumentoParaAssinatura::STATUS_CANCELADO) {
            return ['success' => false, 'message' => 'Este documento não está mais disponível para assinatura.'];
        }
        if ($doc->data_expiracao && $doc->data_expiracao->isPast()) {
            return ['success' => false, 'message' => 'O prazo para assinatura expirou.'];
        }

        $ip = $request->ip();
        $userAgent = $request->userAgent();
        $dataAssinaturaUtc = now('UTC');
        $geolocalizacao = $this->obterGeolocalizacaoPorIp($ip);

        $payloadEvidencia = [
            'documento_id' => $doc->id,
            'signatario_id' => $signatario->id,
            'email' => $signatario->email,
            'nome' => $signatario->nome,
            'cpf' => $cpfInformado ?? $signatario->cpf,
            'consentimento' => true,
            'data_utc' => $dataAssinaturaUtc->toIso8601String(),
            'ip' => $ip,
            'user_agent' => $userAgent,
        ];
        $hashEvidencia = hash('sha256', json_encode($payloadEvidencia));

        DB::transaction(function () use ($signatario, $doc, $ip, $userAgent, $dataAssinaturaUtc, $hashEvidencia, $cpfInformado, $geolocalizacao) {
            $signatario->update([
                'status' => DocumentoAssinaturaSignatario::STATUS_ASSINADO,
                'ip' => $ip,
                'user_agent' => $userAgent,
                'data_assinatura_utc' => $dataAssinaturaUtc,
                'hash_evidencia' => $hashEvidencia,
                'cpf' => $cpfInformado ?? $signatario->cpf,
                'geolocalizacao' => $geolocalizacao,
            ]);

            $this->registrarEvento($doc->id, DocumentoAssinaturaEvento::EVENTO_ASSINADO, [
                'signatario_id' => $signatario->id,
                'email' => $signatario->email,
                'ip' => $ip,
                'data_utc' => $dataAssinaturaUtc->toIso8601String(),
                'hash_evidencia' => $hashEvidencia,
            ]);

            $this->verificarConclusao($doc);
        });

        return ['success' => true, 'message' => 'Documento assinado com sucesso.', 'documento' => $doc->fresh()];
    }

    /**
     * Recusa o documento.
     */
    public function recusar(string $token, Request $request, ?string $motivo = null): array
    {
        $signatario = $this->buscarSignatarioPorToken($token);
        if (!$signatario) {
            return ['success' => false, 'message' => 'Link inválido ou expirado.'];
        }
        if ($signatario->status !== DocumentoAssinaturaSignatario::STATUS_PENDENTE) {
            return ['success' => false, 'message' => 'Este documento já foi processado.'];
        }

        $doc = $signatario->documentoParaAssinatura;

        DB::transaction(function () use ($signatario, $doc, $request, $motivo) {
            $signatario->update([
                'status' => DocumentoAssinaturaSignatario::STATUS_RECUSADO,
                'recusa_motivo' => $motivo,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'data_assinatura_utc' => now('UTC'),
            ]);

            $this->registrarEvento($doc->id, DocumentoAssinaturaEvento::EVENTO_RECUSADO, [
                'signatario_id' => $signatario->id,
                'email' => $signatario->email,
                'motivo' => $motivo,
            ]);
        });

        return ['success' => true, 'message' => 'Documento recusado.', 'documento' => $doc->fresh()];
    }

    /**
     * Verifica se todos assinaram e atualiza status do documento para concluído.
     * Gera o PDF com marca d'água "ASSINADO DIGITALMENTE" e persiste em arquivo_assinado_id.
     */
    protected function verificarConclusao(DocumentoParaAssinatura $doc): void
    {
        $doc->load('signatarios');
        $todosAssinados = $doc->signatarios->every(fn ($s) => $s->status === DocumentoAssinaturaSignatario::STATUS_ASSINADO);
        if ($todosAssinados) {
            $doc->update(['status' => DocumentoParaAssinatura::STATUS_CONCLUIDO]);
            JobFinalizarDocumentoAssinado::dispatch($doc->id);
        }
    }

    /**
     * Processamentos pós-conclusão do documento (pesados), executados em background.
     */
    public function processarPosConclusao(int $documentoId): void
    {
        $doc = DocumentoParaAssinatura::withoutGlobalScopes()->find($documentoId);
        if (!$doc || $doc->status !== DocumentoParaAssinatura::STATUS_CONCLUIDO) {
            return;
        }
        $this->gerarPdfComMarcaAgua($doc);
        $this->aplicarConclusaoCartaOferta($doc);
        JobEnvioDocumentoAssinado::dispatch($doc->id);
    }

    /**
     * Gera cópia do PDF com marca d'água "ASSINADO DIGITALMENTE" e data; persiste em arquivo_assinado_id.
     */
    protected function gerarPdfComMarcaAgua(DocumentoParaAssinatura $doc): void
    {
        $doc->load('arquivo', 'signatarios', 'eventos', 'solicitante');
        $empresa = Cliente::withoutGlobalScopes()->find($doc->empresa_id);
        $doc->setRelation('empresa', $empresa);
        $arquivo = $doc->arquivo;
        if (!$arquivo || $arquivo->disco !== Arquivo::DISCO_DOCUMENTO_ASSINATURA || !$arquivo->file) {
            Log::warning('AssinaturaDigitalService: sem arquivo original para gerar PDF com marca d\'água', ['doc_id' => $doc->id]);
            return;
        }

        try {
            $conteudo = Storage::disk($arquivo->disco)->get($arquivo->file);
            if ($conteudo === null || $conteudo === '') {
                Log::warning('AssinaturaDigitalService: conteúdo do PDF vazio', ['doc_id' => $doc->id]);
                return;
            }

            $tz = 'America/Sao_Paulo';
            $baseUrl = rtrim(config('app.url'), '/');
            $apelido = $empresa && $empresa->apelido ? $empresa->apelido : null;
            $baseVerificacao = $apelido ? $baseUrl . '/' . $apelido . '/assinatura/verificacao' : $baseUrl . '/verificacao-assinatura';
            $signatariosParaPdf = $doc->signatarios
                ->where('status', DocumentoAssinaturaSignatario::STATUS_ASSINADO)
                ->map(function ($s) use ($tz, $baseVerificacao, $doc) {
                    $cpf = $s->cpf ?: '';
                    if ($cpf && strlen(preg_replace('/\D/', '', $cpf)) === 11) {
                        $n = preg_replace('/\D/', '', $cpf);
                        $cpf = substr($n, 0, 3) . '.' . substr($n, 3, 3) . '.' . substr($n, 6, 3) . '-' . substr($n, 9, 2);
                    }
                    // Armazenamento em UTC; exibição em Horário de Brasília (jurisdição brasileira)
                    $raw = $s->getRawOriginal('data_assinatura_utc');
                    $dataBrasilia = $raw ? \Carbon\Carbon::parse($raw, 'UTC')->setTimezone($tz) : null;
                    $timestampBrasiliaIso = $dataBrasilia ? $dataBrasilia->format('Y-m-d\TH:i:sP') : '';
                    $localBr = $dataBrasilia ? $dataBrasilia->format('Y.m.d H:i:s') . " -03'00' (Brasilia)" : '—';
                    $qrPayload = $baseVerificacao . '?d=' . $doc->id . '&s=' . $s->id . '&h=' . ($s->hash_evidencia ?? '');
                    $geo = is_array($s->geolocalizacao) ? $s->geolocalizacao : [];
                    $localTexto = $this->formatarLocalGeolocalizacao($geo);
                    return [
                        'id' => $s->id,
                        'nome' => $s->nome ?: $s->email ?: '—',
                        'email' => $s->email ?: '—',
                        'cpf' => $cpf ?: '—',
                        'data_formatada' => $dataBrasilia ? $dataBrasilia->format('d/m/Y H:i') : '—',
                        'hash_evidencia' => $s->hash_evidencia ?? '',
                        'timestamp_brasilia_iso' => $timestampBrasiliaIso,
                        'data_local_br' => $localBr,
                        'ip' => $s->ip ?? '—',
                        'user_agent' => $s->user_agent ?? '—',
                        'local_assinatura' => $localTexto,
                        'qr_payload' => $qrPayload,
                    ];
                })
                ->values()
                ->toArray();

            $historicoParaPdf = $this->montarHistoricoParaPdf($doc);

            $identificador = $doc->hash_sha256 ?: substr(md5((string) $doc->id), 0, 40);
            $primeiroSignatario = $signatariosParaPdf[0] ?? null;
            $urlVerificacao = $primeiroSignatario['qr_payload'] ?? $baseVerificacao . '?d=' . $doc->id;
            $dadosPagina = [
                'documento_id' => $doc->id,
                'identificador' => $identificador,
                'hash_sha256_pdf' => $doc->hash_sha256 ?? '',
                'url_verificacao' => $urlVerificacao,
                'data_ultima_atualizacao' => $doc->updated_at->setTimezone($tz)->locale('pt_BR')->translatedFormat('d \d\e F \d\e Y \à\s H:i'),
                'data_geracao_ptbr' => now($tz)->locale('pt_BR')->translatedFormat('d \d\e F \d\e Y'),
                'eventos' => $historicoParaPdf,
            ];

            $marcaService = app(PdfMarcaAssinaturaService::class);
            $dataFormatada = now($tz)->format('d/m/Y H:i');
            $pdfComMarca = $marcaService->adicionarMarcaAgua($conteudo, $dataFormatada, $signatariosParaPdf, $dadosPagina);
            if ($pdfComMarca === null) {
                Log::warning('AssinaturaDigitalService: falha ao aplicar marca d\'água', ['doc_id' => $doc->id]);
                return;
            }

            $disco = Arquivo::DISCO_DOCUMENTO_ASSINATURA;
            $nomeBase = pathinfo($arquivo->nome, PATHINFO_FILENAME) ?: 'documento';
            $safeName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $nomeBase) . '_assinado.pdf';
            $path = date('Y/m/d/') . 'doc_' . $doc->id . '_' . uniqid() . '_' . $safeName;

            Storage::disk($disco)->put($path, $pdfComMarca);
            $bytes = strlen($pdfComMarca);

            $arquivoAssinado = Arquivo::create([
                'quem_enviou' => $doc->solicitante_id,
                'nome' => $nomeBase . ' (assinado).pdf',
                'imagem' => false,
                'layout' => null,
                'extensao' => '.pdf',
                'file' => $path,
                'thumb' => null,
                'bytes' => $bytes,
                'temporario' => false,
                'chave' => null,
                'disco' => $disco,
            ]);

            $doc->update(['arquivo_assinado_id' => $arquivoAssinado->id]);
            Log::info('AssinaturaDigitalService: PDF com marca d\'água gerado', ['doc_id' => $doc->id, 'arquivo_assinado_id' => $arquivoAssinado->id]);
        } catch (\Throwable $e) {
            Log::error('AssinaturaDigitalService: erro ao gerar PDF com marca d\'água', [
                'doc_id' => $doc->id,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Obtém geolocalização aproximada (cidade, estado, país) a partir do IP.
     * Usa ipinfo.io (HTTPS, sem chave). Fallback para ip-api.com (HTTP). Não falha a assinatura se as APIs estiverem indisponíveis.
     *
     * @return array{city?: string, regionName?: string, country?: string, lat?: float, lon?: float}
     */
    protected function obterGeolocalizacaoPorIp(string $ip): array
    {
        $ip = trim($ip);
        if ($ip === '' || $ip === '127.0.0.1' || $ip === '::1' || str_starts_with($ip, '192.168.') || str_starts_with($ip, '10.') || str_starts_with($ip, '172.16.')) {
            Log::debug('AssinaturaDigitalService: geolocalização ignorada (IP local/privado)', ['ip' => $ip]);
            return [];
        }
        $result = $this->obterGeolocalizacaoIpinfo($ip);
        if ($result !== []) {
            return $result;
        }
        $result = $this->obterGeolocalizacaoIpApi($ip);
        if ($result !== []) {
            return $result;
        }
        Log::info('AssinaturaDigitalService: geolocalização não obtida para IP', ['ip' => $ip]);
        return [];
    }

    /**
     * ipinfo.io (HTTPS) - funciona em ambientes que bloqueiam HTTP.
     */
    protected function obterGeolocalizacaoIpinfo(string $ip): array
    {
        try {
            $response = Http::timeout(4)->get("https://ipinfo.io/{$ip}/json");
            if (!$response->successful()) {
                return [];
            }
            $data = $response->json();
            if (!is_array($data) || isset($data['error'])) {
                return [];
            }
            $lat = null;
            $lon = null;
            if (!empty($data['loc']) && preg_match('/^([\-0-9.]+),([\-0-9.]+)$/', $data['loc'], $m)) {
                $lat = (float) $m[1];
                $lon = (float) $m[2];
            }
            return [
                'city' => $data['city'] ?? '',
                'regionName' => $data['region'] ?? '',
                'country' => $data['country'] ?? '',
                'lat' => $lat,
                'lon' => $lon,
            ];
        } catch (\Throwable $e) {
            Log::debug('AssinaturaDigitalService: ipinfo.io falhou', ['ip' => $ip, 'message' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * ip-api.com (HTTP) - fallback quando ipinfo.io não estiver disponível.
     */
    protected function obterGeolocalizacaoIpApi(string $ip): array
    {
        try {
            $response = Http::timeout(3)->get("http://ip-api.com/json/{$ip}", [
                'fields' => 'city,regionName,country,lat,lon,status',
            ]);
            if (!$response->successful()) {
                return [];
            }
            $data = $response->json();
            if (!is_array($data) || ($data['status'] ?? '') === 'fail') {
                return [];
            }
            return [
                'city' => $data['city'] ?? '',
                'regionName' => $data['regionName'] ?? '',
                'country' => $data['country'] ?? '',
                'lat' => isset($data['lat']) ? (float) $data['lat'] : null,
                'lon' => isset($data['lon']) ? (float) $data['lon'] : null,
            ];
        } catch (\Throwable $e) {
            Log::debug('AssinaturaDigitalService: ip-api.com falhou', ['ip' => $ip, 'message' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Formata o array de geolocalização para exibição (ex.: "São Paulo, SP, Brasil").
     */
    protected function formatarLocalGeolocalizacao(array $geo): string
    {
        $partes = array_filter([
            $geo['city'] ?? '',
            $geo['regionName'] ?? '',
            $geo['country'] ?? '',
        ]);
        return $partes !== [] ? implode(', ', $partes) : '';
    }

    /**
     * Monta array de entradas do histórico para a página de assinaturas do PDF.
     *
     * @return array [ ['data_formatada' => '20 jan 2025', 'hora' => '11:39:33', 'descricao' => '...'], ... ]
     */
    protected function montarHistoricoParaPdf(DocumentoParaAssinatura $doc): array
    {
        $tz = 'America/Sao_Paulo';
        $lista = [];
        $signatariosPorId = $doc->signatarios->keyBy('id');
        $ipParaLocal = [];
        $empresa = $doc->empresa;

        foreach ($doc->eventos->sortBy('created_at') as $ev) {
            $data = $ev->created_at->setTimezone($tz);
            $payload = is_array($ev->payload) ? $ev->payload : [];
            $nome = $payload['nome'] ?? null;
            $email = $payload['email'] ?? '';
            $cpf = isset($payload['cpf']) && $payload['cpf'] ? $payload['cpf'] : '';
            $ip = $payload['ip'] ?? '';
            if (!$nome && !empty($payload['signatario_id'])) {
                $s = $signatariosPorId->get($payload['signatario_id']);
                if ($s) {
                    $nome = $s->nome ?: $s->email;
                    if (empty($email)) {
                        $email = $s->email ?? '';
                    }
                    if (empty($cpf) && $s->cpf) {
                        $cpf = $s->cpf;
                    }
                }
            }
            if ($cpf && strlen(preg_replace('/\D/', '', $cpf)) === 11) {
                $n = preg_replace('/\D/', '', $cpf);
                $cpf = substr($n, 0, 3) . '.' . substr($n, 3, 3) . '.' . substr($n, 6, 3) . '-' . substr($n, 9, 2);
            }
            $nome = $nome ?: 'Sistema';

            $localTexto = '';
            if ($ip !== '' && $ip !== '—') {
                if (!isset($ipParaLocal[$ip])) {
                    $geo = $this->obterGeolocalizacaoPorIp($ip);
                    $ipParaLocal[$ip] = $this->formatarLocalGeolocalizacao($geo);
                }
                $localTexto = $ipParaLocal[$ip];
            }

            $sufixoLocal = $localTexto !== '' ? ' localizado em ' . $localTexto : '';

            $descricao = '';
            switch ($ev->evento) {
                case DocumentoAssinaturaEvento::EVENTO_ENVIADO:
                    $solicitante = $doc->solicitante;
                    $nomeSol = $solicitante ? $solicitante->nome : 'Sistema';
                    $emailSol = $solicitante && $solicitante->email ? $solicitante->email : '—';
                    $cpfSol = ($solicitante && !empty($solicitante->cpf)) ? $solicitante->cpf : '';
                    if ($cpfSol && strlen(preg_replace('/\D/', '', $cpfSol)) === 11) {
                        $n = preg_replace('/\D/', '', $cpfSol);
                        $cpfSol = substr($n, 0, 3) . '.' . substr($n, 3, 3) . '.' . substr($n, 6, 3) . '-' . substr($n, 9, 2);
                    }
                    $empresaNome = $empresa ? ($empresa->razao_social ?? $empresa->nome_fantasia ?? $empresa->apelido ?? '') : '';
                    $descricao = $nomeSol . ' criou este documento. (Empresa: ' . ($empresaNome ?: '—') . ', Email: ' . $emailSol . ($cpfSol ? ', CPF: ' . $cpfSol : '') . ')';
                    break;
                case DocumentoAssinaturaEvento::EVENTO_VISUALIZADO:
                    $descricao = $nome . ' (Email: ' . $email . ($cpf ? ', CPF: ' . $cpf : '') . ') visualizou este documento por meio do IP ' . ($ip ?: '—') . $sufixoLocal;
                    break;
                case DocumentoAssinaturaEvento::EVENTO_ASSINADO:
                    $descricao = $nome . ' (Email: ' . $email . ($cpf ? ', CPF: ' . $cpf : '') . ') assinou este documento por meio do IP ' . ($ip ?: '—') . $sufixoLocal;
                    break;
                case DocumentoAssinaturaEvento::EVENTO_RECUSADO:
                    $motivo = $payload['motivo'] ?? '';
                    $descricao = $nome . ' (Email: ' . $email . ') recusou este documento.' . ($motivo ? ' Motivo: ' . $motivo : '');
                    break;
                case DocumentoAssinaturaEvento::EVENTO_DOWNLOAD:
                    $descricao = $nome . ' realizou download do documento assinado.' . ($ip ? ' (IP: ' . $ip . ')' : '');
                    break;
                default:
                    $descricao = 'Evento: ' . $ev->evento;
            }

            $lista[] = [
                'data_formatada' => $data->locale('pt_BR')->translatedFormat('d \d\e F \d\e Y'),
                'hora' => $data->format('H:i:s'),
                'descricao' => $descricao,
            ];
        }

        return $lista;
    }

    /**
     * Ao concluir assinatura de uma Carta Oferta, atualiza o registro para dispensar anexo manual.
     */
    protected function aplicarConclusaoCartaOferta(DocumentoParaAssinatura $doc): void
    {
        if ($doc->tipo_documento !== 'carta_oferta' || $doc->documentable_type !== CartaOferta::class) {
            return;
        }
        $carta = CartaOferta::withoutGlobalScopes()->find($doc->documentable_id);
        if (!$carta) {
            return;
        }
        $logs = is_array($carta->logs) ? $carta->logs : [];
        $logs[] = [
            'data' => (new DataHora())->dataHoraInsert(),
            'mensagem' => 'Carta de oferta assinada digitalmente',
            'status' => 'Assinado digitalmente',
            'usuario' => 'Sistema (assinatura digital)',
        ];
        $carta->update([
            'status' => CartaOferta::STATUS_AGUARDANDO_RH,
            'arquivo_id' => $doc->arquivo_id,
            'logs' => $logs,
        ]);
    }

    /**
     * Lista documentos para assinatura da empresa (uso interno, com scope).
     */
    public function listar(int $empresaId, array $filtros = [])
    {
        $query = DocumentoParaAssinatura::with(['signatarios', 'arquivo', 'solicitante'])
            ->where('empresa_id', $empresaId)
            ->orderBy('created_at', 'desc');

        if (!empty($filtros['status'])) {
            $query->where('status', $filtros['status']);
        }
        if (!empty($filtros['tipo_documento'])) {
            $query->where('tipo_documento', $filtros['tipo_documento']);
        }
        if (!empty($filtros['solicitante_id'])) {
            $query->where('solicitante_id', (int) $filtros['solicitante_id']);
        }
        if (!empty(trim($filtros['signatario'] ?? ''))) {
            $termo = '%' . trim($filtros['signatario']) . '%';
            $query->whereHas('signatarios', function ($q) use ($termo) {
                $q->where(function ($q2) use ($termo) {
                    $q2->where('nome', 'like', $termo)
                        ->orWhere('email', 'like', $termo)
                        ->orWhere('cpf', 'like', $termo);
                });
            });
        }
        if (!empty($filtros['data_inicio'])) {
            $query->whereDate('created_at', '>=', $filtros['data_inicio']);
        }
        if (!empty($filtros['data_fim'])) {
            $query->whereDate('created_at', '<=', $filtros['data_fim']);
        }
        if (!empty($filtros['id'])) {
            $idOrToken = $filtros['id'];
            if (is_numeric($idOrToken)) {
                $query->where('id', (int) $idOrToken);
            } else {
                $query->where('token', $idOrToken);
            }
        }

        return $query->paginate((int) ($filtros['per_page'] ?? 15));
    }

    /**
     * Cancela um documento (apenas rascunho ou em_assinatura).
     */
    public function cancelar(int $documentoId, int $empresaId): array
    {
        $doc = DocumentoParaAssinatura::where('id', $documentoId)->where('empresa_id', $empresaId)->first();
        if (!$doc) {
            return ['success' => false, 'message' => 'Documento não encontrado.'];
        }
        if (!in_array($doc->status, [DocumentoParaAssinatura::STATUS_RASCUNHO, DocumentoParaAssinatura::STATUS_EM_ASSINATURA])) {
            return ['success' => false, 'message' => 'Este documento não pode ser cancelado.'];
        }

        $doc->update(['status' => DocumentoParaAssinatura::STATUS_CANCELADO]);
        $this->registrarEvento($doc->id, DocumentoAssinaturaEvento::EVENTO_CANCELADO, ['user_id' => auth()->id()]);

        return ['success' => true, 'message' => 'Documento cancelado.'];
    }

    protected function chaveCacheCodigoVerificacao(int $signatarioId): string
    {
        return 'assinatura:codigo_verificacao:' . $signatarioId;
    }

    protected function chaveCacheTentativasCodigo(int $signatarioId): string
    {
        return 'assinatura:codigo_tentativas:' . $signatarioId;
    }

    protected function chaveCacheCooldownCodigo(int $signatarioId): string
    {
        return 'assinatura:codigo_cooldown:' . $signatarioId;
    }
}
