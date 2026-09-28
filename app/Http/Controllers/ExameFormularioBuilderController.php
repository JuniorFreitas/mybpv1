<?php

namespace App\Http\Controllers;

use App\Domain\Exames\Services\ExameFormularioBuilderService;
use App\Domain\Exames\Services\ExameFormularioPayloadNormalizer;
use App\Domain\Exames\Services\ExameFormularioResolver;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExameFormularioBuilderController extends Controller
{
    public function __construct(
        private readonly ExameFormularioBuilderService $builder,
        private readonly ExameFormularioResolver $resolver,
        private readonly ExameFormularioPayloadNormalizer $normalizer
    ) {
    }

    public function index(): JsonResponse
    {
        $this->authorize('cadastro_empresa_exame');

        return response()->json($this->builder->listarFormulariosEmpresa($this->empresaIdObrigatoria()));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('cadastro_empresa_exame');

        $validator = \Validator::make($request->all(), [
            'titulo' => 'required|string|max:255',
            'descricao' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['msg' => 'Erro de validação', 'erros' => $validator->errors()], 400);
        }

        try {
            $form = $this->builder->criarFormulario(
                $this->empresaIdObrigatoria(),
                $request->input('titulo'),
                $request->input('descricao')
            );

            return response()->json($this->normalizer->toFrontend($form), 201);
        } catch (DomainException $e) {
            return response()->json(['msg' => $e->getMessage()], 400);
        }
    }

    public function show($formulario): JsonResponse
    {
        $this->authorize('cadastro_empresa_exame');

        try {
            $form = $this->builder->carregar((int) $formulario, $this->empresaIdObrigatoria());

            return response()->json($this->normalizer->toFrontend($form));
        } catch (DomainException $e) {
            return response()->json(['msg' => $e->getMessage()], 404);
        }
    }

    public function update(Request $request, $formulario): JsonResponse
    {
        $this->authorize('cadastro_empresa_exame');

        try {
            $form = $this->builder->atualizarFormulario(
                (int) $formulario,
                $this->empresaIdObrigatoria(),
                $request->only(['titulo', 'descricao'])
            );

            return response()->json($this->normalizer->toFrontend($form));
        } catch (DomainException $e) {
            return response()->json(['msg' => $e->getMessage()], 400);
        }
    }

    public function porTipo($exameTipo, Request $request): JsonResponse
    {
        $contexto = $request->get('contexto', 'encaminhamento');
        $empresaId = $this->empresaId();

        $form = $contexto === 'resultado'
            ? $this->resolver->resolverResultado((int) $exameTipo, $empresaId)
            : $this->resolver->resolverEncaminhamento((int) $exameTipo, $empresaId);

        if (!$form) {
            return response()->json(['msg' => 'Formulário não encontrado.'], 404);
        }

        return response()->json($this->normalizer->toFrontend($form));
    }

    public function storeSetor(Request $request, $formulario): JsonResponse
    {
        $this->authorize('cadastro_empresa_exame');

        $validator = \Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json(['msg' => 'Erro de validação', 'erros' => $validator->errors()], 400);
        }

        try {
            $this->builder->carregar((int) $formulario, $this->empresaIdObrigatoria());
            $setor = $this->builder->adicionarSetor(
                (int) $formulario,
                $this->empresaIdObrigatoria(),
                $request->input('nome')
            );

            return response()->json($setor, 201);
        } catch (DomainException $e) {
            return response()->json(['msg' => $e->getMessage()], 400);
        }
    }

    public function updateSetor(Request $request, $setor): JsonResponse
    {
        $this->authorize('cadastro_empresa_exame');

        try {
            $setorModel = $this->builder->atualizarSetor(
                (int) $setor,
                $this->empresaIdObrigatoria(),
                $request->input('nome', '')
            );

            return response()->json($setorModel);
        } catch (DomainException $e) {
            return response()->json(['msg' => $e->getMessage()], 400);
        }
    }

    public function reorderSetores(Request $request, $formulario): JsonResponse
    {
        $this->authorize('cadastro_empresa_exame');

        $ids = $request->input('setor_ids', []);
        if (!is_array($ids)) {
            return response()->json(['msg' => 'setor_ids inválido'], 400);
        }

        try {
            $this->builder->reordenarSetores((int) $formulario, $this->empresaIdObrigatoria(), $ids);

            return response()->json(['ok' => true]);
        } catch (DomainException $e) {
            return response()->json(['msg' => $e->getMessage()], 400);
        }
    }

    public function storeCampo(Request $request, $setor): JsonResponse
    {
        $this->authorize('cadastro_empresa_exame');

        $validator = \Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'tipo' => ['required', Rule::in(ExameFormularioBuilderService::TIPOS_CAMPO)],
            'obrigatorio' => 'nullable|boolean',
            'min' => 'nullable|integer',
            'max' => 'nullable|integer',
            'opcoes' => 'nullable|array',
            'chave_canonica' => 'nullable|string|max:64',
            'class_especial' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['msg' => 'Erro de validação', 'erros' => $validator->errors()], 400);
        }

        try {
            $campo = $this->builder->adicionarCampo(
                (int) $setor,
                $this->empresaIdObrigatoria(),
                $validator->validated() + ['obrigatorio' => $request->boolean('obrigatorio')]
            );

            return response()->json($campo, 201);
        } catch (DomainException $e) {
            return response()->json(['msg' => $e->getMessage()], 400);
        }
    }

    public function updateCampo(Request $request, $alternativa): JsonResponse
    {
        $this->authorize('cadastro_empresa_exame');

        try {
            $campo = $this->builder->atualizarCampo(
                (int) $alternativa,
                $this->empresaIdObrigatoria(),
                $request->all()
            );

            return response()->json($campo);
        } catch (DomainException $e) {
            return response()->json(['msg' => $e->getMessage()], 400);
        }
    }

    public function reorderCampos(Request $request, $setor): JsonResponse
    {
        $this->authorize('cadastro_empresa_exame');

        $ids = $request->input('alternativa_ids', []);
        if (!is_array($ids)) {
            return response()->json(['msg' => 'alternativa_ids inválido'], 400);
        }

        try {
            $this->builder->reordenarCampos((int) $setor, $this->empresaIdObrigatoria(), $ids);

            return response()->json(['ok' => true]);
        } catch (DomainException $e) {
            return response()->json(['msg' => $e->getMessage()], 400);
        }
    }

    public function destroyCampo(Request $request, $setor, $alternativa): JsonResponse
    {
        $this->authorize('cadastro_empresa_exame');

        try {
            $this->builder->desativarCampo(
                (int) $setor,
                (int) $alternativa,
                $this->empresaIdObrigatoria()
            );

            return response()->json([], 204);
        } catch (DomainException $e) {
            return response()->json(['msg' => $e->getMessage()], 400);
        }
    }

    private function empresaId(): ?int
    {
        $id = auth()->user()->empresa_id ?? null;

        return $id !== null ? (int) $id : null;
    }

    private function empresaIdObrigatoria(): int
    {
        $id = $this->empresaId();
        if (!$id) {
            throw new DomainException('Usuário sem empresa vinculada.');
        }

        return $id;
    }
}
