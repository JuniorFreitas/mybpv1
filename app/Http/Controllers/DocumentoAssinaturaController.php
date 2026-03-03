<?php

namespace App\Http\Controllers;

use App\Jobs\AssinaturaDigital\JobEnvioEmailAssinatura;
use App\Jobs\JobExportaExcel;
use App\Jobs\JobExportaPdf;
use App\Models\Arquivo;
use App\Models\DocumentoAssinaturaEvento;
use App\Models\DocumentoParaAssinatura;
use App\Models\ClienteConfig;
use App\Services\AssinaturaDigital\AssinaturaCotaService;
use App\Services\AssinaturaDigital\AssinaturaDigitalService;
use Illuminate\Http\Request;
use MasterTag\DataHora;

class DocumentoAssinaturaController extends Controller
{
    protected AssinaturaDigitalService $service;
    protected AssinaturaCotaService $cotaService;

    public function __construct(AssinaturaDigitalService $service, AssinaturaCotaService $cotaService)
    {
        $this->service = $service;
        $this->cotaService = $cotaService;
    }

    /**
     * Exibe a página de gerenciamento de documentos para assinatura.
     */
    public function indexView()
    {
        return view('g.administracao.documento-assinatura.index');
    }

    /**
     * Lista documentos para assinatura da empresa (multi-tenant por empresa_id do usuário).
     */
    public function index(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;
        $filtros = $request->only(['status', 'tipo_documento', 'solicitante_id', 'signatario', 'data_inicio', 'data_fim', 'id', 'per_page', 'page']);
        $filtros['per_page'] = $filtros['per_page'] ?? $request->get('porPagina', 15);
        $lista = $this->service->listar($empresaId, $filtros);
        $resumoAssinaturas = $this->cotaService->obterResumoMensal($empresaId, $request->get('referencia'));

        return response()->json([
            'atual' => $lista->currentPage(),
            'ultima' => $lista->lastPage(),
            'total' => $lista->total(),
            'dados' => [
                'itens' => $lista->items(),
                'resumo_assinaturas' => $resumoAssinaturas,
            ],
        ]);
    }

    public function config()
    {
        $empresaId = auth()->user()->empresa_id;
        $resumo = $this->cotaService->obterResumoMensal($empresaId);
        $opcoes = $this->cotaService->listarUsuariosEGrupos($empresaId);
        $config = \App\Models\ClienteConfig::whereClienteId($empresaId)->first();

        return response()->json([
            'limite_assinaturas_mensal' => $config ? $config->limite_assinaturas_mensal : null,
            'assinatura_alerta_user_ids' => $config && is_array($config->assinatura_alerta_user_ids) ? $config->assinatura_alerta_user_ids : [],
            'assinatura_alerta_grupo_ids' => $config && is_array($config->assinatura_alerta_grupo_ids) ? $config->assinatura_alerta_grupo_ids : [],
            'usuarios' => $opcoes['usuarios'],
            'grupos' => $opcoes['grupos'],
            'resumo_assinaturas' => $resumo,
        ]);
    }

    public function salvarConfig(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;
        $dados = $request->validate([
            'limite_assinaturas_mensal' => 'nullable|integer|min:0',
            'assinatura_alerta_user_ids' => 'nullable|array',
            'assinatura_alerta_user_ids.*' => 'integer|exists:users,id',
            'assinatura_alerta_grupo_ids' => 'nullable|array',
            'assinatura_alerta_grupo_ids.*' => 'integer|exists:papeis,id',
        ]);

        $this->cotaService->salvarConfig($empresaId, [
            'limite_assinaturas_mensal' => array_key_exists('limite_assinaturas_mensal', $dados) ? $dados['limite_assinaturas_mensal'] : null,
            'assinatura_alerta_user_ids' => $dados['assinatura_alerta_user_ids'] ?? [],
            'assinatura_alerta_grupo_ids' => $dados['assinatura_alerta_grupo_ids'] ?? [],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Configurações de cota de assinatura salvas com sucesso.',
        ]);
    }

    public function exportarExtrato(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;
        $userId = auth()->id();
        $dados = $request->validate([
            'formato' => 'required|in:xlsx,pdf',
            'referencia' => 'nullable|string|regex:/^\d{4}-\d{2}$/',
        ]);

        $resumo = $this->cotaService->obterResumoMensal($empresaId, $dados['referencia'] ?? null);
        $competencia = str_replace('-', '_', $resumo['competencia']);

        if ($dados['formato'] === 'xlsx') {
            $head = ['Competência', 'Tipo Documento', 'Quantidade'];
            $rows = [];
            foreach ($resumo['extrato_por_tipo'] as $item) {
                $rows[] = [$resumo['competencia'], $item['label'], $item['total']];
            }
            if (empty($rows)) {
                $rows[] = [$resumo['competencia'], 'Sem registros', 0];
            }

            $nomeArquivo = "assinatura_extrato_{$competencia}_" . rand(1000, 9999) . '_' . date('YmdHis') . '.xlsx';
            JobExportaExcel::dispatch($userId, 'Assinatura Digital - Extrato Mensal', $head, $rows, $nomeArquivo);
        } else {
            $nomeArquivo = "assinatura_extrato_{$competencia}_" . rand(1000, 9999) . '_' . date('YmdHis') . '.pdf';
            $view = 'pdf.administracao.documentoassinatura.extrato-mensal';
            $model = [
                'resumo' => $resumo,
                'gerado_em' => (new DataHora())->dataHoraInsert(),
            ];
            /** @var \App\Models\User $user */
            $user = auth()->user();
            JobExportaPdf::dispatch($user->toArray(), 'Assinatura Digital - Extrato Mensal', $model, $nomeArquivo, $view);
        }

        return response()->json([
            'success' => true,
            'message' => 'Estamos gerando o extrato. Você será notificado ao concluir.',
        ]);
    }

    /**
     * Lista solicitantes (usuários que enviaram ao menos um documento) para o filtro.
     */
    public function solicitantes()
    {
        $empresaId = auth()->user()->empresa_id;
        $ids = DocumentoParaAssinatura::where('empresa_id', $empresaId)
            ->whereNotNull('solicitante_id')
            ->distinct()
            ->pluck('solicitante_id');
        $usuarios = \App\Models\User::whereIn('id', $ids)
            ->orderBy('nome')
            ->get(['id', 'nome'])
            ->map(fn ($u) => ['id' => $u->id, 'nome' => $u->nome ?? $u->name ?? '—']);
        return response()->json($usuarios);
    }

    /**
     * Detalhe de um documento com signatários e eventos (auditoria).
     * Aceita id numérico ou token (hash) na URL.
     */
    public function show($idOrToken)
    {
        $empresaId = auth()->user()->empresa_id;
        $doc = DocumentoParaAssinatura::with(['signatarios', 'eventos', 'arquivo', 'arquivoAssinado', 'solicitante'])
            ->where('empresa_id', $empresaId)
            ->porIdOuToken($idOrToken)
            ->firstOrFail();

        return response()->json($doc);
    }

    /**
     * Exporta evidencias completas de um documento (auditoria).
     * Aceita id numérico ou token (hash) na URL.
     */
    public function exportarEvidencias(Request $request, $idOrToken)
    {
        $empresaId = auth()->user()->empresa_id;
        $doc = DocumentoParaAssinatura::with(['signatarios', 'eventos', 'arquivo', 'arquivoAssinado', 'solicitante'])
            ->where('empresa_id', $empresaId)
            ->porIdOuToken($idOrToken)
            ->firstOrFail();

        $config = ClienteConfig::whereClienteId($empresaId)->first();
        $exibirIpCompleto = $this->flagExibirCompleto($config ? $config->assinatura_exibir_ip_completo : null);
        $exibirCpfCompleto = $this->flagExibirCompleto($config ? $config->assinatura_exibir_cpf_completo : null);

        $payload = [
            'gerado_em_utc' => now('UTC')->toIso8601String(),
            'empresa_id' => $doc->empresa_id,
            'documento' => [
                'id' => $doc->id,
                'token' => $doc->token,
                'tipo_documento' => $doc->tipo_documento,
                'status' => $doc->status,
                'data_expiracao' => optional($doc->data_expiracao)->toIso8601String(),
                'hash_sha256' => $doc->hash_sha256,
                'arquivo_id' => $doc->arquivo_id,
                'arquivo_assinado_id' => $doc->arquivo_assinado_id,
                'solicitante_id' => $doc->solicitante_id,
                'consentimento_ultimo_em' => optional($doc->consentimento_ultimo_em)->toIso8601String(),
                'consentimento_ultimo_signatario_id' => $doc->consentimento_ultimo_signatario_id,
            ],
            'signatarios' => $doc->signatarios->map(function ($s) use ($exibirCpfCompleto, $exibirIpCompleto) {
                return [
                    'id' => $s->id,
                    'user_id' => $s->user_id,
                    'email' => $s->email,
                    'nome' => $s->nome,
                    'cpf' => $this->formatarCpf($s->cpf, $exibirCpfCompleto),
                    'ordem' => $s->ordem,
                    'token' => $s->token,
                    'status' => $s->status,
                    'ip' => $this->formatarIp($s->ip, $exibirIpCompleto),
                    'user_agent' => $s->user_agent,
                    'data_assinatura_utc' => optional($s->data_assinatura_utc)->toIso8601String(),
                    'geolocalizacao' => $s->geolocalizacao,
                    'hash_evidencia' => $s->hash_evidencia,
                    'recusa_motivo' => $s->recusa_motivo,
                    'consentimento_assinatura' => (bool) $s->consentimento_assinatura,
                    'consentimento_em' => optional($s->consentimento_em)->toIso8601String(),
                ];
            })->values(),
            'eventos' => $doc->eventos->map(function ($e) {
                return [
                    'id' => $e->id,
                    'evento' => $e->evento,
                    'payload' => $e->payload,
                    'created_at_utc' => optional($e->created_at)->toIso8601String(),
                ];
            })->values(),
        ];

        $user = auth()->user();
        $this->service->registrarEvento($doc->id, DocumentoAssinaturaEvento::EVENTO_EXPORTADO, [
            'user_id' => $user->id,
            'nome' => $user->nome ?? $user->name ?? 'Sistema',
            'email' => $user->email ?? null,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'data_utc' => now('UTC')->toIso8601String(),
            'formato' => $request->get('format') === 'pdf' ? 'pdf' : 'json',
        ]);

        if ($request->get('format') === 'pdf') {
            $view = 'pdf.administracao.documentoassinatura.evidencias';
            $dados = [
                'evidencias' => $payload,
                'exibir_ip_completo' => $exibirIpCompleto,
                'exibir_cpf_completo' => $exibirCpfCompleto,
            ];
            $filename = 'assinatura_evidencias_' . $doc->id . '.pdf';
            /** @var \Barryvdh\DomPDF\PDF $pdf */
            $pdf = app('dompdf.wrapper');
            $pdf->loadView($view, $dados);
            return $pdf->download($filename);
        }

        if ($request->boolean('download')) {
            $json = json_encode($payload, JSON_PRETTY_PRINT);
            $filename = 'assinatura_evidencias_' . $doc->id . '.json';
            return response()->streamDownload(function () use ($json) {
                echo $json;
            }, $filename, ['Content-Type' => 'application/json']);
        }

        return response()->json($payload);
    }

    /**
     * Cria documento de assinatura a partir de um PDF já existente (arquivo_id).
     */
    public function criarComArquivoExistente(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;
        $payload = $request->validate([
            'arquivo_id' => 'required|integer',
            'tipo_documento' => 'required|string|max:80',
            'documentable_type' => 'required|string|max:180',
            'documentable_id' => 'required|integer',
            'signatarios' => 'required|array|min:1',
            'signatarios.*.email' => 'required|email',
            'signatarios.*.nome' => 'required|string|max:120',
            'signatarios.*.cpf' => 'nullable|string|max:20',
            'ordem_assinatura' => 'nullable|string|max:20',
            'data_expiracao' => 'nullable|date',
            'solicitante_id' => 'nullable|integer',
        ]);

        $ordem = $payload['ordem_assinatura'] ?? DocumentoParaAssinatura::ORDEM_SEQUENCIAL;
        $solicitanteId = $payload['solicitante_id'] ?? auth()->id();
        $dataExpiracao = !empty($payload['data_expiracao']) ? new \DateTime($payload['data_expiracao']) : null;

        $doc = $this->service->criarEnvioComArquivoExistente(
            $empresaId,
            (int) $payload['arquivo_id'],
            $payload['tipo_documento'],
            $payload['documentable_type'],
            (int) $payload['documentable_id'],
            $solicitanteId,
            $payload['signatarios'],
            $ordem,
            $dataExpiracao
        );

        return response()->json([
            'success' => true,
            'documento_id' => $doc->id,
            'token' => $doc->token,
        ], 201);
    }

    private function flagExibirCompleto(?bool $valor): bool
    {
        return $valor === null ? true : (bool) $valor;
    }

    private function formatarCpf(?string $cpf, bool $exibirCompleto): ?string
    {
        if (!$cpf) {
            return null;
        }
        if ($exibirCompleto) {
            return $cpf;
        }
        return '***.***.***-**';
    }

    private function formatarIp(?string $ip, bool $exibirCompleto): ?string
    {
        if (!$ip) {
            return null;
        }
        if ($exibirCompleto) {
            return $ip;
        }
        if (strpos($ip, '.') !== false) {
            $parts = explode('.', $ip);
            if (count($parts) === 4) {
                $parts[3] = '***';
                return implode('.', $parts);
            }
        }
        if (strpos($ip, ':') !== false) {
            $parts = explode(':', $ip);
            $count = count($parts);
            for ($i = max(0, $count - 4); $i < $count; $i++) {
                $parts[$i] = '****';
            }
            return implode(':', $parts);
        }
        return $ip;
    }

    /**
     * Download do documento assinado (PDF com marca d'água). Apenas quando status = concluído.
     * Aceita id numérico ou token (hash) na URL.
     */
    public function downloadAssinado($idOrToken)
    {
        $empresaId = auth()->user()->empresa_id;
        $doc = DocumentoParaAssinatura::with('arquivoAssinado')
            ->where('empresa_id', $empresaId)
            ->porIdOuToken($idOrToken)
            ->firstOrFail();

        if ($doc->status !== DocumentoParaAssinatura::STATUS_CONCLUIDO) {
            abort(404, 'Documento ainda não está concluído. Aguarde todas as assinaturas.');
        }
        if (!$doc->arquivo_assinado_id || !$doc->arquivoAssinado) {
            abort(404, 'Documento assinado não encontrado.');
        }

        $arquivo = $doc->arquivoAssinado;
        if (!$arquivo->disco || !$arquivo->file) {
            abort(404, 'Arquivo inválido.');
        }

        $user = auth()->user();
        $this->service->registrarEvento($doc->id, DocumentoAssinaturaEvento::EVENTO_DOWNLOAD, [
            'user_id' => $user->id,
            'nome' => $user->nome ?? $user->name ?? 'Sistema',
            'email' => $user->email ?? null,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return Arquivo::anexoDownload($arquivo->disco, $arquivo->file);
    }

    /**
     * Cancela um documento (rascunho ou em_assinatura).
     * Aceita id numérico ou token (hash) na URL.
     */
    public function cancelar(Request $request, $idOrToken)
    {
        $empresaId = auth()->user()->empresa_id;
        $doc = DocumentoParaAssinatura::where('empresa_id', $empresaId)->porIdOuToken($idOrToken)->first();
        if (!$doc) {
            return response()->json(['success' => false, 'message' => 'Documento não encontrado.'], 404);
        }
        $result = $this->service->cancelar($doc->id, $empresaId);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Reenvia e-mail de assinatura para os signatários do documento.
     * Aceita id numérico ou token (hash) na URL.
     */
    public function reenviarEmail($idOrToken)
    {
        $empresaId = auth()->user()->empresa_id;
        $doc = DocumentoParaAssinatura::where('empresa_id', $empresaId)->porIdOuToken($idOrToken)->first();
        if (!$doc) {
            return response()->json(['success' => false, 'message' => 'Documento não encontrado.'], 404);
        }
        if ($doc->status !== DocumentoParaAssinatura::STATUS_EM_ASSINATURA) {
            return response()->json(['success' => false, 'message' => 'Só é possível reenviar e-mail para documentos em assinatura.'], 422);
        }

        JobEnvioEmailAssinatura::dispatch($doc->id);

        $user = auth()->user();
        $this->service->registrarEvento($doc->id, DocumentoAssinaturaEvento::EVENTO_REENVIADO, [
            'user_id' => $user->id,
            'nome' => $user->nome ?? $user->name ?? 'Sistema',
        ]);

        return response()->json(['success' => true, 'message' => 'E-mail de assinatura reenviado para os signatários.']);
    }

}
