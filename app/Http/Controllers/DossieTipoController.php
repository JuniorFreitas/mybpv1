<?php

namespace App\Http\Controllers;

use App\Http\Requests\Documentos\StoreDossieTipoRequest;
use App\Http\Requests\Documentos\UpdateDossieTipoRequest;
use App\Models\Arquivo;
use App\Models\Sistema;
use App\Models\User;
use App\Services\Dossie\DossieTipoCadastroService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DossieTipoController extends Controller
{
    public function __construct(private readonly DossieTipoCadastroService $cadastroService)
    {
    }

    public function index(): View
    {
        return view('g.cadastros.dossietipos.index');
    }

    public function store(StoreDossieTipoRequest $request): JsonResponse
    {
        $this->authorize('cadastro_dossie_tipos_insert');

        try {
            $dados = $request->validated();
            $dados['modelo'] = $request->input('modelo', []);
            $dados['modeloDel'] = $request->input('modeloDel', []);
            $this->cadastroService->criar($dados, $this->empresaIdObrigatoria());

            return response()->json([], 201);
        } catch (DomainException $e) {
            return response()->json(['msg' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            $this->logErro('STORE DOSSIE TIPO', $e);

            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    public function edit($dossietipo): JsonResponse
    {
        $this->authorize('cadastro_dossie_tipos');

        try {
            $tipo = $this->cadastroService->buscarParaEdicao((int) $dossietipo, $this->empresaId());

            return response()->json($tipo);
        } catch (DomainException $e) {
            return response()->json(['msg' => $e->getMessage()], 404);
        }
    }

    public function update(UpdateDossieTipoRequest $request, $dossietipo): JsonResponse
    {
        $this->authorize('cadastro_dossie_tipos_update');

        try {
            $dados = $request->validated();
            $dados['modelo'] = $request->input('modelo', []);
            $dados['modeloDel'] = $request->input('modeloDel', []);
            $this->cadastroService->atualizar((int) $dossietipo, $dados, $this->empresaId());

            return response()->json([], 201);
        } catch (DomainException $e) {
            $status = $e->getMessage() === 'Tipo de dossiê não encontrado.' ? 404 : 400;

            return response()->json(['msg' => $e->getMessage()], $status);
        } catch (\Exception $e) {
            $this->logErro('UPDATE DOSSIE TIPO', $e);

            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    public function atualizar(Request $request): JsonResponse
    {
        $this->authorize('cadastro_dossie_tipos');

        $porPagina = (int) $request->get('porPagina', $request->get('pages', 20));
        $page = (int) $request->get('page', 1);

        $resultado = $this->cadastroService->listar(
            $this->empresaId(),
            [
                'campoBusca' => $request->get('campoBusca'),
                'campoStatus' => $request->get('campoStatus'),
                'campoEscopo' => $request->get('campoEscopo'),
            ],
            $porPagina,
            $page
        );

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'items' => $resultado->items(),
                'empresa_id' => $this->empresaId(),
                'assinatura_digital_habilitada' => Sistema::assinaturaDigitalHabilitada($this->empresaId()),
            ],
        ], 200);
    }

    public function ativaDesativa(Request $request, $dossietipo): JsonResponse
    {
        $this->authorize('cadastro_dossie_tipos');

        try {
            $tipo = $this->cadastroService->alternarAtivo((int) $dossietipo, $this->empresaId());

            return response()->json(['ativo' => $tipo->ativo], 201);
        } catch (DomainException $e) {
            return response()->json(['msg' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            $this->logErro('ATIVA DESATIVA DOSSIE TIPO', $e);

            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    public function uploadAnexos(Request $request): JsonResponse
    {
        $this->authorize('cadastro_dossie_tipos');

        return Arquivo::uploadAnexos($request, Arquivo::MIMEAPENASIMAGENSPDF, Arquivo::DISCO_DOSSIE);
    }

    public function anexoShow($arquivo)
    {
        $this->authorize('cadastro_dossie_tipos');

        return Arquivo::anexoShow(Arquivo::DISCO_DOSSIE, $arquivo);
    }

    public function anexoDownload($arquivo)
    {
        $this->authorize('cadastro_dossie_tipos');

        return Arquivo::anexoDownload(Arquivo::DISCO_DOSSIE, $arquivo);
    }

    public function anexoDelete($arquivo)
    {
        $this->authorize('cadastro_dossie_tipos');

        return Arquivo::anexoDelete(Arquivo::DISCO_DOSSIE, $arquivo);
    }

    private function empresaId(): ?int
    {
        $empresaId = auth()->user()->empresa_id ?? null;

        return $empresaId !== null ? (int) $empresaId : null;
    }

    private function empresaIdObrigatoria(): int
    {
        $empresaId = $this->empresaId();
        if (!$empresaId) {
            throw new DomainException('Usuário sem empresa vinculada.');
        }

        return $empresaId;
    }

    private function logErro(string $contexto, \Exception $e): void
    {
        $usuario = User::find(auth()->id());
        $nome = $usuario?->nome ?? 'desconhecido';
        \Log::debug("error {$contexto}: {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: {$nome}");
    }
}
