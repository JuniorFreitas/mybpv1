<?php

namespace App\Http\Controllers;

use App\Domain\Whatsapp\Enums\TipoMensagemWhatsapp;
use App\Domain\Whatsapp\Services\WhatsappConfigService;
use App\Domain\Whatsapp\Services\WhatsappMessageFactory;
use App\Http\Requests\WhatsappUsuarioPreferenciaAdminRequest;
use App\Services\Whatsapp\WhatsappUsuarioNotificacaoService;
use App\Http\Requests\WhatsappConfigRequest;
use App\Http\Requests\WhatsappTemplateRequest;
use App\Models\ClienteConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WhatsappConfigController extends Controller
{
    public function __construct(
        private readonly WhatsappConfigService $configService,
        private readonly WhatsappMessageFactory $messageFactory,
        private readonly WhatsappUsuarioNotificacaoService $usuarioNotificacaoService,
    ) {
    }

    public function index()
    {
        $this->authorizeAccess();

        return view('g.configuracoes.whatsapp.index');
    }

    public function status(): JsonResponse
    {
        if (!auth()->check()) {
            abort(401);
        }

        $empresaId = $this->resolveEmpresaId(request());

        return response()->json([
            ...$this->configService->getStatusForApi($empresaId),
            'empresa_id' => $empresaId,
        ]);
    }

    public function showConfig(): JsonResponse
    {
        $this->authorizeAccess();
        $empresaId = $this->resolveEmpresaId(request());

        $whatsappLiberado = ClienteConfig::query()
            ->where('cliente_id', $empresaId)
            ->value('envia_whatsapp');

        return response()->json([
            'config' => $this->configService->getConfigForApi($empresaId),
            'whatsapp_liberado' => (bool) $whatsappLiberado,
            'readonly' => !$this->canEdit(),
        ]);
    }

    public function updateConfig(WhatsappConfigRequest $request): JsonResponse
    {
        $this->authorizeAccess(true);

        $empresaId = $this->resolveEmpresaId($request);
        $this->configService->saveConfig($empresaId, $request->validated());

        return response()->json([
            'success' => true,
            'config' => $this->configService->getConfigForApi($empresaId),
        ]);
    }

    public function updateModulos(Request $request): JsonResponse
    {
        $this->authorizeAccess(true);

        $request->validate([
            'modulos_habilitados' => 'required|array|min:1',
            'modulos_habilitados.*.modulo' => 'required|string|max:80',
            'modulos_habilitados.*.habilitado' => 'required|boolean',
        ]);

        $empresaId = $this->resolveEmpresaId($request);
        $this->configService->saveModulosHabilitados($empresaId, $request->input('modulos_habilitados', []));

        return response()->json([
            'success' => true,
            'modulos_habilitados' => $this->configService->listModulosHabilitadosForApi($empresaId),
        ]);
    }

    public function listTemplates(): JsonResponse
    {
        $this->authorizeAccess();
        $empresaId = $this->resolveEmpresaId(request());

        return response()->json($this->configService->listTemplatesForApi($empresaId));
    }

    public function showTemplate(string $tipo): JsonResponse
    {
        $this->authorizeAccess();
        $tipoEnum = $this->resolveTipo($tipo);
        $empresaId = $this->resolveEmpresaId(request());

        $templates = collect($this->configService->listTemplatesForApi($empresaId));
        $item = $templates->firstWhere('tipo_mensagem', $tipoEnum->value);

        if (!$item) {
            return response()->json(['msg' => 'Tipo não encontrado'], 404);
        }

        return response()->json($item);
    }

    public function updateTemplate(string $tipo, WhatsappTemplateRequest $request): JsonResponse
    {
        $this->authorizeAccess(true);
        $tipoEnum = $this->resolveTipo($tipo);
        $empresaId = $this->resolveEmpresaId($request);

        $template = $this->configService->saveTemplate(
            $empresaId,
            $tipoEnum,
            $request->input('corpo'),
            $request->boolean('ativo', true)
        );

        return response()->json([
            'success' => true,
            'template' => $template,
        ]);
    }

    public function destroyTemplate(string $tipo): JsonResponse
    {
        $this->authorizeAccess(true);
        $tipoEnum = $this->resolveTipo($tipo);
        $empresaId = $this->resolveEmpresaId(request());

        $this->configService->restoreTemplatePadrao($empresaId, $tipoEnum);

        return response()->json(['success' => true]);
    }

    public function previewTemplate(string $tipo, WhatsappTemplateRequest $request): JsonResponse
    {
        $this->authorizeAccess();
        $tipoEnum = $this->resolveTipo($tipo);
        $empresaId = $this->resolveEmpresaId(request());
        $contexto = $request->input('contexto', []);

        $mensagem = $this->messageFactory->preview($tipoEnum, $empresaId, $contexto);

        if ($request->filled('corpo')) {
            $contact = $this->configService->resolveContactData($empresaId);
            $global = [
                'empresa_nome' => $contact['nome_exibicao'],
                'empresa_telefone' => $contact['telefone_contato'],
                'empresa_endereco' => $contact['endereco_completo'],
                'assinatura' => $contact['texto_assinatura'] ?: '*Equipe ' . $contact['nome_exibicao'] . '*',
                'rodape_mybp' => config('whatsapp_templates.rodape_padrao', ''),
            ];
            $renderer = app(\App\Domain\Whatsapp\Services\WhatsappTemplateRenderer::class);
            $mensagem = $renderer->render($request->input('corpo'), array_merge($global, $contexto));
            $mensagem = $this->messageFactory->garantirRodapeMybp($mensagem);
        }

        return response()->json(['mensagem' => $mensagem]);
    }

    public function previewFluxo(Request $request): JsonResponse
    {
        $this->authorizeAccess();

        $request->validate([
            'tipo_mensagem' => 'required|string',
            'contexto' => 'required|array',
            'empresa_id' => 'nullable|integer',
        ]);

        $tipoEnum = $this->resolveTipo($request->input('tipo_mensagem'));
        $empresaId = $this->resolveEmpresaId($request);

        $mensagem = $this->messageFactory->render(
            $tipoEnum,
            $empresaId,
            $request->input('contexto', [])
        );

        return response()->json(['mensagem' => $mensagem]);
    }

    public function tipos(): JsonResponse
    {
        $this->authorizeAccess();

        return response()->json(TipoMensagemWhatsapp::catalogo());
    }

    public function listarUsuariosNotificacoes(Request $request): JsonResponse
    {
        $this->authorizeAccess();
        $empresaId = $this->resolveEmpresaId($request);

        $paginator = $this->usuarioNotificacaoService->listarUsuarios($empresaId, [
            'busca' => $request->input('busca'),
            'apto_whatsapp' => $request->input('apto_whatsapp'),
            'recebe_movimentacao' => $request->input('recebe_movimentacao'),
            'por_pagina' => $request->input('por_pagina', 25),
        ]);

        return response()->json([
            'whatsapp_liberado' => (bool) ClienteConfig::query()
                ->where('cliente_id', $empresaId)
                ->value('envia_whatsapp'),
            'modulos' => $this->configService->listModulosHabilitadosForApi($empresaId),
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function atualizarPreferenciaUsuario(
        WhatsappUsuarioPreferenciaAdminRequest $request,
        int $userId,
    ): JsonResponse {
        $this->authorizeAccess(true);
        $empresaId = $this->resolveEmpresaId($request);

        $item = $this->usuarioNotificacaoService->atualizarPreferenciaUsuario(
            $empresaId,
            $userId,
            $request->input('modulo'),
            $request->boolean('receber'),
        );

        return response()->json([
            'success' => true,
            'usuario' => $item,
        ]);
    }

    private function resolveTipo(string $tipo): TipoMensagemWhatsapp
    {
        $enum = TipoMensagemWhatsapp::tryFromString($tipo);

        if (!$enum) {
            abort(404, 'Tipo de mensagem inválido.');
        }

        return $enum;
    }

    private function resolveEmpresaId(Request $request): int
    {
        if ($request->filled('empresa_id') && auth()->user()->can('administracao_clientes')) {
            return (int) $request->input('empresa_id');
        }

        return (int) auth()->user()->empresa_id;
    }

    private function authorizeAccess(bool $requireEdit = false): void
    {
        if ($requireEdit && !$this->canEdit()) {
            abort(403, 'Sem permissão para editar configurações de WhatsApp.');
        }

        if (!$this->canView()) {
            abort(403, 'Sem permissão para acessar configurações de WhatsApp.');
        }
    }

    private function canView(): bool
    {
        $user = auth()->user();

        return $user->can('configuracao_whatsapp') || $user->can('administracao_clientes');
    }

    private function canEdit(): bool
    {
        $user = auth()->user();

        if ($user->can('administracao_clientes')) {
            return true;
        }

        if (!$user->can('configuracao_whatsapp')) {
            return false;
        }

        $whatsappLiberado = ClienteConfig::query()
            ->where('cliente_id', $user->empresa_id)
            ->value('envia_whatsapp');

        return (bool) $whatsappLiberado;
    }
}
