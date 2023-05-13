<?php

namespace App\Http\Controllers;

use App\Models\Arquivo;
use App\Models\Cliente;
use App\Models\Curriculo;
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
        $empresaId = $this->getEmpresa();
        if(is_null($empresaId)){
            abort(404);
        }
        return view('documentos.index');
    }

    protected function getEmpresa(){
       return Cliente::withoutGlobalScopes()->select('id')->whereApelido(request()->segment(1))->first();
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
            ->whereHas('Feedback', function ($q) use ($empresaId){
                $q->withoutGlobalScopes();
                $q->whereEmpresaId($empresaId);
                $q->whereHas('ResultadoIntegrado', function ($qu) {
                    $qu->whereDocumentosEntregue(true);
                });
            })
            ->with('Telefones',
                'FotoTres',
                'AnexosCpfRg',
                'ComprovanteEnd',
                'CtpsFrente',
                'CtpsVerso',
                'Antecedentes',
                'TituloEleitor',
                'CertificadoReservista',
                'PisRescisao',
                'CertificadoEscolaridade',
                'ContaBanco',
                'CartaSindicato',
                'CarteiraVacina',
                'RgcpfFilho',
                'CartaoVacinaFilho',
                'DeclaracaoEscolarFilho',
                'CartaOferta'
            );
        if ($candidato->count() == 0) {
            return response()->json(['msg' => 'Não foi possivel autenticar, CPF e/ou Data de Nascimento inválido. Ou você já inseriu todos os documentos.', 'autenticado' => false], 400);
        } else {
            $candidato = $candidato->first();
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

                if (isset($dados['telefones'])) {
                    foreach ($dados['telefones'] as $linha) {
                        if (isset($linha['id'])) {
                            $curriculo->Telefones()->find($linha['id'])->update($linha);
                        } else {
                            $curriculo->Telefones()->create($linha);
                        }
                    }
                }

                //Remove a foto de anexo
                if (isset($dados['foto_tresDel'])) {
                    foreach ($dados['foto_tresDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();
                    }
                }
                // inseri uma nova foto de anexo
                if (isset($dados['foto_tres'])) {
                    foreach ($dados['foto_tres'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $curriculo->FotoTres()->attach($arquivo->id, ['tipo' => 'foto3x4']);
                        }
                    }
                }

                //Remove a foto de anexos_cpf_rgDel
                if (isset($dados['anexos_cpf_rgDel'])) {
                    foreach ($dados['anexos_cpf_rgDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();
                    }
                }
                // inseri uma nova foto de anexos_cpf_rg
                if (isset($dados['anexos_cpf_rg'])) {
                    foreach ($dados['anexos_cpf_rg'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $curriculo->AnexosCpfRg()->attach($arquivo->id, ['tipo' => 'anexoscpfrg']);
                        }
                    }
                }

                //Remove a foto de anexos_cpf_rgDel
                if (isset($dados['comprovante_endDel'])) {
                    foreach ($dados['comprovante_endDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();
                    }
                }
                // inseri uma nova foto de comprovante_end
                if (isset($dados['comprovante_end'])) {
                    foreach ($dados['comprovante_end'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $curriculo->ComprovanteEnd()->attach($arquivo->id, ['tipo' => 'comprovante_end']);
                        }
                    }
                }

                //Remove a foto de anexos_cpf_rgDel
                if (isset($dados['ctps_frenteDel'])) {
                    foreach ($dados['ctps_frenteDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();
                    }
                }
                // inseri uma nova foto de ctps_frente
                if (isset($dados['ctps_frente'])) {
                    foreach ($dados['ctps_frente'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $curriculo->CtpsFrente()->attach($arquivo->id, ['tipo' => 'ctps_frente']);
                        }
                    }
                }

                //Remove a foto de anexos_cpf_rgDel
                if (isset($dados['ctps_versoDel'])) {
                    foreach ($dados['ctps_versoDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();
                    }
                }
                // inseri uma nova foto de ctps_verso
                if (isset($dados['ctps_verso'])) {
                    foreach ($dados['ctps_verso'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $curriculo->CtpsVerso()->attach($arquivo->id, ['tipo' => 'ctps_verso']);
                        }
                    }
                }

                //Remove a foto de anexos_cpf_rgDel
                if (isset($dados['antecedentesDel'])) {
                    foreach ($dados['antecedentesDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();
                    }
                }
                // inseri uma nova foto de antecedentes
                if (isset($dados['antecedentes'])) {
                    foreach ($dados['antecedentes'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $curriculo->Antecedentes()->attach($arquivo->id, ['tipo' => 'antecedentes']);
                        }
                    }
                }

                //Remove a foto de anexos_cpf_rgDel
                if (isset($dados['titulo_eleitorDel'])) {
                    foreach ($dados['titulo_eleitorDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();
                    }
                }
                // inseri uma nova foto de titulo_eleitor
                if (isset($dados['titulo_eleitor'])) {
                    foreach ($dados['titulo_eleitor'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $curriculo->TituloEleitor()->attach($arquivo->id, ['tipo' => 'titulo_eleitor']);
                        }
                    }
                }

                //Remove a foto de anexos_cpf_rgDel
                if (isset($dados['certificado_reservistaDel'])) {
                    foreach ($dados['certificado_reservistaDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();
                    }
                }
                // inseri uma nova foto de certificado_reservista
                if (isset($dados['certificado_reservista'])) {
                    foreach ($dados['certificado_reservista'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $curriculo->CertificadoReservista()->attach($arquivo->id, ['tipo' => 'certificado_reservista']);
                        }
                    }
                }

                //Remove a foto de anexos_cpf_rgDel
                if (isset($dados['pis_rescisaoDel'])) {
                    foreach ($dados['pis_rescisaoDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();
                    }
                }
                // inseri uma nova foto de pis_rescisao
                if (isset($dados['pis_rescisao'])) {
                    foreach ($dados['pis_rescisao'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $curriculo->PisRescisao()->attach($arquivo->id, ['tipo' => 'pis_rescisao']);
                        }
                    }
                }

                //Remove a foto de anexos_cpf_rgDel
                if (isset($dados['certificado_escolaridadeDel'])) {
                    foreach ($dados['certificado_escolaridadeDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();
                    }
                }
                // inseri uma nova foto de certificado_escolaridade
                if (isset($dados['certificado_escolaridade'])) {
                    foreach ($dados['certificado_escolaridade'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $curriculo->CertificadoEscolaridade()->attach($arquivo->id, ['tipo' => 'certificado_escolaridade']);
                        }
                    }
                }

                //Remove a foto de anexos_cpf_rgDel
                if (isset($dados['conta_bancoDel'])) {
                    foreach ($dados['conta_bancoDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();
                    }
                }
                // inseri uma nova foto de conta_banco
                if (isset($dados['conta_banco'])) {
                    foreach ($dados['conta_banco'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $curriculo->ContaBanco()->attach($arquivo->id, ['tipo' => 'conta_banco']);
                        }
                    }
                }

                //Remove a foto de anexos_cpf_rgDel
                if (isset($dados['carta_sindicatoDel'])) {
                    foreach ($dados['carta_sindicatoDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();
                    }
                }
                // inseri uma nova foto de carta_sindicato
                if (isset($dados['carta_sindicato'])) {
                    foreach ($dados['carta_sindicato'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $curriculo->CartaSindicato()->attach($arquivo->id, ['tipo' => 'carta_sindicato']);
                        }
                    }
                }

                //Remove a foto de anexos_cpf_rgDel
                if (isset($dados['carteira_vacinaDel'])) {
                    foreach ($dados['carteira_vacinaDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();
                    }
                }
                // inseri uma nova foto de carteira_vacina
                if (isset($dados['carteira_vacina'])) {
                    foreach ($dados['carteira_vacina'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $curriculo->CarteiraVacina()->attach($arquivo->id, ['tipo' => 'carteira_vacina']);
                        }
                    }
                }

                //Remove a foto de anexos_cpf_rgDel
                if (isset($dados['rgcpf_filhoDel'])) {
                    foreach ($dados['rgcpf_filhoDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();
                    }
                }
                // inseri uma nova foto de rgcpf_filho
                if (isset($dados['rgcpf_filho'])) {
                    foreach ($dados['rgcpf_filho'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $curriculo->CarteiraVacina()->attach($arquivo->id, ['tipo' => 'rgcpf_filho']);
                        }
                    }
                }

                //Remove a foto de anexos_cpf_rgDel
                if (isset($dados['cartao_vacina_filhoDel'])) {
                    foreach ($dados['cartao_vacina_filhoDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();
                    }
                }
                // inseri uma nova foto de cartao_vacina_filho
                if (isset($dados['cartao_vacina_filho'])) {
                    foreach ($dados['cartao_vacina_filho'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $curriculo->CartaoVacinaFilho()->attach($arquivo->id, ['tipo' => 'cartao_vacina_filho']);
                        }
                    }
                }

                //Remove a foto de anexos_cpf_rgDel
                if (isset($dados['declaracao_escolar_filhoDel'])) {
                    foreach ($dados['declaracao_escolar_filhoDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();
                    }
                }
                // inseri uma nova foto de declaracao_escolar_filho
                if (isset($dados['declaracao_escolar_filho'])) {
                    foreach ($dados['declaracao_escolar_filho'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $curriculo->DeclaracaoEscolarFilho()->attach($arquivo->id, ['tipo' => 'declaracao_escolar_filho']);
                        }
                    }
                }

                //Remove a carta oferta
                if (isset($dados['carta_oferta'])) {
                    foreach ($dados['carta_ofertaDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();
                    }
                }
                // inseri carta oferta
                if (isset($dados['carta_oferta'])) {
                    foreach ($dados['carta_oferta'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $curriculo->CartaOferta()->attach($arquivo->id, ['tipo' => 'carta_oferta']);
                        }
                    }
                }

                DB::commit();
                return response()->json([], 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "error UPDATE DOCUMENTOS PRÉ ADMISSÃO:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user();
                \Log::debug($msg);
                return response()->json(['msg' => $msg], 400);
//                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
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
