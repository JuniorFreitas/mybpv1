<?php

namespace App\Http\Controllers;

use App\Models\Arquivo;
use App\Models\Cliente;
use App\Models\Curriculo;
use App\Models\DocumentosCurriculosAdmissaoEmpresa;
use App\Models\DocumentosPreAdmissao;
use App\Models\ResultadoIntegrado;
use App\Models\Sistema;
use App\Models\User;
use App\Jobs\JobComprovanteDocumentosPreAdmissao;
use App\Services\AcessoPublico\AuditoriaAcessoService;
use App\Services\Marca\MarcaClienteService;
use App\Services\Preadmissao\AnexoPreAdmissaoAcessoService;
use App\Services\Preadmissao\DocumentosPreAdmissaoAcessoService;
use App\Services\Preadmissao\TermoDocumentosPreAdmissao;
use DB;
use Illuminate\Http\Request;
use MasterTag\DataHora;
use RuntimeException;

class DocumentosPreAdmissaoController extends Controller
{
    /**
     * Tela pública do candidato: `/{apelido}/documentos` (link geral) ou `/{apelido}/documentos/{publicId}`.
     */
    public function index(Request $request, DocumentosPreAdmissaoAcessoService $acesso, MarcaClienteService $marca, ?string $apelido = null, ?string $publicId = null)
    {
        auth()->logout();
        $empresa = $acesso->encontrarEmpresa((string) $request->segment(1));
        if (!$empresa) {
            abort(404);
        }

        $payload = ['autenticado' => false, 'public_id' => $publicId];
        $sessao = $this->sessaoDoLink($request, $acesso, $empresa, $publicId);

        if ($sessao) {
            [$dadosSessao, $registro] = $sessao;
            if (!empty($dadosSessao['autenticado'])) {
                $payload = $acesso->payloadAutenticado($registro, (int) $empresa->id) + ['public_id' => $publicId];
                app(AnexoPreAdmissaoAcessoService::class)->liberarCandidato($request, (int) $acesso->curriculoId($registro));
            } else {
                $payload += [
                    'desafio_pendente' => true,
                    'email_mascarado' => $acesso->emailMascarado($registro),
                    'acesso' => $acesso->statusAcesso($registro),
                ];
            }
        }

        $payload['termo'] = app(TermoDocumentosPreAdmissao::class)->montar($empresa);

        return view('documentos.index', [
            'payload' => $payload,
            'marca' => $marca->dados($empresa),
        ]);
    }

    public function solicitarCodigo(Request $request, DocumentosPreAdmissaoAcessoService $acesso)
    {
        $request->validate([
            'cpf' => 'nullable|string|min:11|max:20',
            'public_id' => 'nullable|uuid',
        ]);

        $empresa = $acesso->encontrarEmpresa((string) $request->segment(1));
        if (!$empresa) {
            return response()->json(['message' => 'Empresa não encontrada.'], 404);
        }

        $publicId = $request->input('public_id');
        $chave = $this->sessionKey($empresa->apelido);
        $registro = null;

        try {
            if ($request->filled('cpf')) {
                // "Já recebi o código": só confere o CPF, sem enviar outro código.
                $jaTemCodigo = $request->boolean('ja_tenho_codigo');
                $resultado = $jaTemCodigo
                    ? $acesso->confirmarCpf($empresa, $request->input('cpf'), $publicId)
                    : $acesso->solicitarCodigo($empresa, $request->input('cpf'), $publicId);
                $registro = $resultado['registro'];
                session([$chave => ['resultado_id' => $registro->id, 'autenticado' => false]]);
                $evento = $jaTemCodigo ? AuditoriaAcessoService::EVENTO_CPF_CONFIRMADO : AuditoriaAcessoService::EVENTO_CODIGO_SOLICITADO;
                $this->auditar($request, $acesso, $empresa, $evento, $registro, [
                    'email_mascarado' => $resultado['resposta']['email_mascarado'],
                ]);

                return response()->json($resultado['resposta'], 200);
            }

            // Reenvio: só para quem já confirmou o CPF nesta sessão.
            $sessao = $this->sessaoDoLink($request, $acesso, $empresa, $publicId);
            if (!$sessao) {
                return response()->json(['message' => 'Informe seu CPF para receber o código.'], 422);
            }
            $registro = $sessao[1];
            $resposta = $acesso->reenviarCodigo($registro);
            $this->auditar($request, $acesso, $empresa, AuditoriaAcessoService::EVENTO_CODIGO_REENVIADO, $registro, [
                'email_mascarado' => $resposta['email_mascarado'],
            ]);

            return response()->json($resposta, 200);
        } catch (RuntimeException $e) {
            $this->auditar($request, $acesso, $empresa, AuditoriaAcessoService::EVENTO_CODIGO_NEGADO, $registro, [
                'cpf' => $request->input('cpf'),
                'dados' => ['motivo' => $e->getMessage(), 'public_id' => $publicId],
            ]);

            return response()->json([
                'message' => $e->getMessage(),
                'acesso' => $registro && session($chave) ? $acesso->statusAcesso($registro) : null,
            ], 422);
        }
    }

    public function validarCodigo(Request $request, DocumentosPreAdmissaoAcessoService $acesso)
    {
        $request->validate([
            'codigo' => 'required|string|min:8|max:12',
            'public_id' => 'nullable|uuid',
        ]);

        $empresa = $acesso->encontrarEmpresa((string) $request->segment(1));
        if (!$empresa) {
            return response()->json(['message' => 'Empresa não encontrada.'], 404);
        }

        $sessao = $this->sessaoDoLink($request, $acesso, $empresa, $request->input('public_id'));
        if (!$sessao) {
            return response()->json(['message' => 'Informe seu CPF para receber o código.'], 422);
        }
        $registro = $sessao[1];

        try {
            $acesso->validarCodigo($registro, $request->input('codigo'));
            $payload = $acesso->payloadAutenticado($registro, (int) $empresa->id);
            session([$this->sessionKey($empresa->apelido) => ['resultado_id' => $registro->id, 'autenticado' => true]]);
            app(AnexoPreAdmissaoAcessoService::class)->liberarCandidato($request, (int) $acesso->curriculoId($registro));
            $this->auditar($request, $acesso, $empresa, AuditoriaAcessoService::EVENTO_CODIGO_VALIDADO, $registro);

            return response()->json($payload, 200);
        } catch (RuntimeException $e) {
            $this->auditar($request, $acesso, $empresa, AuditoriaAcessoService::EVENTO_CODIGO_INVALIDO, $registro, [
                'dados' => ['motivo' => $e->getMessage()],
            ]);

            return response()->json([
                'message' => $e->getMessage(),
                'acesso' => $acesso->statusAcesso($registro),
            ], 422);
        }
    }

    /**
     * Evidência do ato na trilha de auditoria (IP, navegador, data/hora e CPF mascarado).
     */
    protected function auditar(Request $request, DocumentosPreAdmissaoAcessoService $acesso, Cliente $empresa, string $evento, ?ResultadoIntegrado $registro, array $info = [])
    {
        return app(AuditoriaAcessoService::class)->registrar(
            DocumentosPreAdmissaoAcessoService::CONTEXTO,
            (int) $empresa->id,
            $evento,
            $request,
            $info + ($registro ? [
                'registro_id' => $registro->id,
                'curriculo_id' => $acesso->curriculoId($registro),
                'cpf' => $acesso->cpfDoCandidato($registro),
            ] : [])
        );
    }

    protected function sessionKey(string $apelido): string
    {
        return 'documentos_pre_adm_' . $apelido;
    }

    /**
     * Sessão de acesso válida para este link: [dados da sessão, solicitação de documentos].
     * Com `$publicId`, a sessão só vale se for da mesma solicitação do link.
     *
     * @return array{0: array, 1: \App\Models\ResultadoIntegrado}|null
     */
    protected function sessaoDoLink(Request $request, DocumentosPreAdmissaoAcessoService $acesso, Cliente $empresa, ?string $publicId): ?array
    {
        $dados = $request->session()->get($this->sessionKey($empresa->apelido));
        if (!is_array($dados) || empty($dados['resultado_id'])) {
            return null;
        }

        $registro = $acesso->encontrarPorId((int) $empresa->id, (int) $dados['resultado_id']);
        if (!$registro || ($publicId && $registro->public_id !== $publicId)) {
            return null;
        }

        return [$dados, $registro];
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\DocumentosPreAdmissao $documentosPreAdmissao
     * @return \Illuminate\Http\Response
     */
    public function show(DocumentosPreAdmissao $documentosPreAdmissao)
    {
        //
    }

    public function edit($documentosPreAdmissao)
    {

    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\DocumentosPreAdmissao $documentosPreAdmissao
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function update(Request $request, DocumentosPreAdmissaoAcessoService $acesso)
    {
        $empresa = $acesso->encontrarEmpresa((string) $request->segment(1));
        $sessao = $empresa ? $this->sessaoDoLink($request, $acesso, $empresa, null) : null;
        if (!$sessao || empty($sessao[0]['autenticado'])) {
            return response()->json(['msg' => 'Sua sessão expirou. Acesse novamente com CPF e código.'], 401);
        }

        // O currículo vem da sessão autenticada, nunca do corpo da requisição.
        $curriculoId = $acesso->curriculoId($sessao[1]);

        $dados = $request->input();

        // O aceite precisa ser do texto vigente: se o termo mudou com a tela aberta, pede para recarregar.
        $termo = app(TermoDocumentosPreAdmissao::class)->montar($empresa);
        if (!$request->boolean('termo_aceito')) {
            return response()->json(['msg' => 'Para enviar, é necessário aceitar o termo de declaração e ciência.'], 422);
        }
        if ($request->input('termo_hash') !== $termo['hash']) {
            return response()->json(['msg' => 'O termo foi atualizado. Recarregue a página e aceite novamente.'], 422);
        }

        $dadosValidados = \Validator::make($dados, [
            'nome' => 'required',
            'filiacao_mae' => 'required',
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Salvar Informações',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();

                $curriculo = Curriculo::withoutGlobalScopes()->find($curriculoId);

                $curriculo->update([
                    'nome' => $dados['nome'],
                    'filiacao_pai' => $dados['filiacao_pai'],
                    'filiacao_mae' => $dados['filiacao_mae'],
                ]);

                if (isset($dados['telefonesDelete'])) {
                    foreach ($dados['telefonesDelete'] as $telefonesDelete) {
                        $curriculo->Telefones()->find($telefonesDelete)->delete();
                    }
                }

                foreach ($dados['telefones'] as $linha) {
                    $linha['principal'] = $linha['principal'] == 'true';
                    if ($linha['id'] == 0) {
                        $telPrincipal = $curriculo->Telefones()->create($linha)->id;
                        if ($linha['principal']) {
                            $dados['telefone_id'] = $telPrincipal;
                        }
                    } else {
                        $curriculo->Telefones->find($linha['id'])->update($linha);
                        if ($linha['principal']) {
                            $dados['telefone_id'] = $linha['id'];
                        }
                    }
                }

                foreach ($request->docs_curriculo_pre_adm as $doc) {
                    if (isset($doc['docs_curriculo_anexosDelete'])) {
                        foreach ($doc['docs_curriculo_anexosDelete'] as $anexo) {
                            // Só exclui anexos que pertencem ao próprio candidato.
                            $pertence = DB::table('documentos_curriculos')
                                ->where('curriculo_id', $curriculo->id)
                                ->where('arquivo_id', $anexo)
                                ->exists();
                            if ($pertence) {
                                Arquivo::find($anexo)?->excluir();
                            }
                        }
                    }

                    if (isset($doc['docs_curriculo_anexos'])) {
                        foreach ($doc['docs_curriculo_anexos'] as $anexo) {
                            $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                            if ($arquivo) {
                                $arquivo->temporario = false;
                                $arquivo->chave = '';
                                $arquivo->save();
                                DB::table('documentos_curriculos')->updateOrInsert(
                                    ['curriculo_id' => $curriculo->id, 'arquivo_id' => $arquivo->id],
                                    ['tipo' => $doc['tipo']]);
                            }
                        }
                    }
                }

                DB::commit();

                $protocolo = app(AuditoriaAcessoService::class)->novoProtocolo('DOC');
                $this->auditar($request, $acesso, $empresa, AuditoriaAcessoService::EVENTO_TERMO_ACEITO, $sessao[1], [
                    'protocolo' => $protocolo,
                    'dados' => ['termo' => $termo],
                ]);
                $this->auditar($request, $acesso, $empresa, AuditoriaAcessoService::EVENTO_DOCUMENTOS_ENVIADOS, $sessao[1], [
                    'protocolo' => $protocolo,
                    'dados' => [
                        'arquivo_ids' => DB::table('documentos_curriculos')
                            ->where('curriculo_id', $curriculo->id)
                            ->orderBy('arquivo_id')
                            ->pluck('arquivo_id')
                            ->all(),
                    ],
                ]);

                JobComprovanteDocumentosPreAdmissao::dispatch([
                    'empresa_id' => (int) $empresa->id,
                    'registro_id' => (int) $sessao[1]->id,
                    'curriculo_id' => (int) $curriculo->id,
                    'protocolo' => $protocolo,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'termo_versao' => $termo['versao'],
                ]);

                return response()->json(['protocolo' => $protocolo], 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "error UPDATE DOCUMENTOS PRÉ ADMISSÃO:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . $dados['nome'];
                \Log::debug($msg);
                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\DocumentosPreAdmissao $documentosPreAdmissao
     * @return \Illuminate\Http\Response
     */
    public function destroy(DocumentosPreAdmissao $documentosPreAdmissao)
    {
        //
    }

    // Anexos (regras de acesso em AnexoPreAdmissaoAcessoService) ---------------
    public function uploadAnexos(Request $request, AnexoPreAdmissaoAcessoService $anexos)
    {
        if (!$anexos->podeEnviar($request)) {
            return response()->json(['msg' => 'Sua sessão expirou. Acesse novamente com CPF e código.'], 401);
        }

        $resposta = Arquivo::uploadAnexos($request, Arquivo::MIMEAPENASIMAGENSPDF, Arquivo::DISCO_DOCUMENTOS_PRE_ADMISSAO);
        if ($resposta->getStatusCode() === 201) {
            $anexos->registrarEnvio($request, (int) ($resposta->getData(true)['id'] ?? 0));
        }

        return $resposta;
    }

    public function anexoShow(Request $request, AnexoPreAdmissaoAcessoService $anexos, $arquivo)
    {
        abort_unless($anexos->podeAcessar($request, $arquivo), 403);

        if ($request->query('thumb')) {
            $extensaoArquivo = substr($arquivo, strrpos($arquivo, '.') + 1);
            $nomeArquivo = substr($arquivo, 0, strrpos($arquivo, '.'));
            $arquivo = $nomeArquivo . '_p.' . $extensaoArquivo;
        }

        return Arquivo::anexoShow(Arquivo::DISCO_DOCUMENTOS_PRE_ADMISSAO, $arquivo);
    }

    public function anexoDelete(Request $request, AnexoPreAdmissaoAcessoService $anexos, $arquivo)
    {
        abort_unless($anexos->podeAcessar($request, $arquivo), 403);

        return Arquivo::anexoDelete(Arquivo::DISCO_DOCUMENTOS_PRE_ADMISSAO, $arquivo);
    }

    public function download(Request $request, AnexoPreAdmissaoAcessoService $anexos, $arquivo)
    {
        abort_unless($anexos->podeAcessar($request, $arquivo), 403);

        return Arquivo::anexoDownload(Arquivo::DISCO_DOCUMENTOS_PRE_ADMISSAO, $arquivo);
    }
}
