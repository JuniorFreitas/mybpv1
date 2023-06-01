<?php

namespace App\Http\Controllers;

use App\Models\Arquivo;
use App\Models\Cliente;
use App\Models\Curriculo;
use App\Models\DocumentosCurriculosAdmissaoEmpresa;
use App\Models\DocumentosPreAdmissao;
use App\Models\Sistema;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use MasterTag\DataHora;

class DocumentosPreAdmissaoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index()
    {
        auth()->logout();
        $empresaId = $this->getEmpresa();
        if (is_null($empresaId)) {
            abort(404);
        }
        return view('documentos.index');
    }

    protected function getEmpresa()
    {
        return DB::table('clientes')->select('id')->whereApelido(request()->segment(1))->first();
    }

    public function autenticar(Request $request)
    {
        $empresaId = $this->getEmpresa()->id;
        $cpf = Sistema::transformCpfCnpj($request->cpf);

        $dataNascimento = Sistema::dataTransform($request->nascimento);
        if (!Sistema::validaCPF($cpf)) {
            return response()->json(['msg' => 'CPF inválido'], 400);
        }

        $candidato = Curriculo::withoutGlobalScopes()->whereCpf($cpf)
            ->whereNascimento((new DataHora($dataNascimento))->dataInsert())
            ->whereHas('Feedback', function ($q) use ($empresaId) {
                $q->withoutGlobalScopes();
                $q->whereEmpresaId($empresaId);
                $q->whereHas('ResultadoIntegrado', function ($qu) {
                    $qu->whereDocumentosEntregue(true);
                });
            })->with(['Telefones'])
            ->first();


        $candidato->docs_curriculo_pre_adm = DocumentosCurriculosAdmissaoEmpresa::getDocumentoCurriculoAdmissaoEmpresa($empresaId)
            ->transform(function ($doc) use ($candidato) {
                $doc->docs_curriculo_anexos = \Illuminate\Support\Facades\DB::table('documentos_curriculos')
                    ->whereTipo($doc->tipo)
                    ->where('curriculo_id', $candidato->id)
                    ->join('arquivos', 'arquivos.id', '=', 'documentos_curriculos.arquivo_id')
                    ->get()->transform(function ($doc) {
                        $doc->url = "";
                        $doc->url_download = "";
                        if (in_array($doc->disco, Arquivo::LISTAGEM_DISCOS)) {
                            $doc->url = config('filesystems.disks.' . $doc->disco . '.urlShow') . "/{$doc->file}";
                            $doc->urlDownload = config('filesystems.disks.' . $doc->disco . '.urlDownload') . "/{$doc->file}";
                            $doc->urlThumb = config('filesystems.disks.' . $doc->disco . '.urlThumb') . "/{$doc->file}";
                        };
                        return $doc;
                    });
                $doc->docs_curriculo_anexosDelete = [];
                $doc->qnt_anexos = count($doc->docs_curriculo_anexos);
                return $doc;
            });

        if (is_null($candidato)) {
            return response()->json(['msg' => 'Não foi possivel autenticar, CPF e/ou Data de Nascimento inválido. Ou você já inseriu todos os documentos.', 'autenticado' => false], 400);
        } else {
            return response()->json(['curriculo' => $candidato, 'autenticado' => true]);
        }

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
    public function update(Request $request, DocumentosPreAdmissao $documentosPreAdmissao)
    {

        $dados = $request->input();

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

                $curriculo = Curriculo::withoutGlobalScopes()->find($dados['id']);

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
                            $arquivo = Arquivo::find($anexo);
                            $arquivo->excluir();
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
                return response()->json([], 201);
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

    // Anexos-------------------------------------------------
    public function uploadAnexos(Request $request)
    {
        return Arquivo::uploadAnexos($request, Arquivo::MIMEAPENASIMAGENSPDF, Arquivo::DISCO_DOCUMENTOS_PRE_ADMISSAO);
    }

    public function anexoShow(Request $request, $arquivo)
    {
        return Arquivo::anexoShow(Arquivo::DISCO_DOCUMENTOS_PRE_ADMISSAO, $arquivo);
    }

    public function anexoDelete(Request $request, $arquivo)
    {
        return Arquivo::anexoDelete(Arquivo::DISCO_DOCUMENTOS_PRE_ADMISSAO, $arquivo);
    }

    public function download(Request $request, $arquivo)
    {
        return Arquivo::anexoDownload(Arquivo::DISCO_DOCUMENTOS_PRE_ADMISSAO, $arquivo);
    }
}
