<?php

namespace App\Http\Controllers;

use App\Jobs\Entrevista\JobEnvioDocumento;
use App\Mail\Entrevista\EnvioDocumentosMail;
use App\Models\Admissao;
use App\Models\Arquivo;
use App\Models\Curriculo;
use App\Models\DocumentosCurriculosAdmissaoEmpresa;
use App\Models\DocumentosPreAdmissao;
use App\Models\FeedbackCurriculo;
use App\Models\Sistema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use MasterTag\DataHora;

class PreAdmissaoController extends Controller
{

    public function index()
    {
        return view('g.admissao.preadmissao.index');
    }

    public function show($feedback)
    {
        $feedback = FeedbackCurriculo::select(['id', 'curriculo_id', 'vaga_id', 'vaga_projeto_id', 'vagas_abertas_id'])
            ->whereId($feedback)
            ->first()
            ->load('Curriculo:id,nome,cpf,email,nascimento,rg,orgao_expeditor,logradouro,complemento,bairro,municipio,uf');

        $feedback->docs_curriculo_pre_adm = DocumentosCurriculosAdmissaoEmpresa::getDocumentoCurriculoAdmissaoEmpresa(auth()->user()->empresa_id)
            ->transform(function ($doc) use ($feedback) {
                $doc->docs_curriculo_anexos = DB::table('documentos_curriculos')
                    ->whereTipo($doc->tipo)
                    ->where('curriculo_id', $feedback->curriculo_id)
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
                $doc->qnt_anexos = count($doc->docs_curriculo_anexos);
                return $doc;
            });

        return $feedback;

    }



    public function atualizar(Request $request)
    {

        $resultado = FeedbackCurriculo::select(['id', 'curriculo_id', 'vaga_id', 'vaga_projeto_id', 'vagas_abertas_id'])->with(['Curriculo' => function ($model) {
            $model->select(['id', 'nome', 'cpf', 'email', 'nascimento', 'rg', 'orgao_expeditor', 'logradouro', 'complemento', 'bairro', 'municipio', 'uf']);
        }]);

        if ($request->filled('status')) {
            if ($request->status == 'em_processo') {
                $resultado->whereHas('ResultadoIntegrado', function ($q) {
                    $q->whereDocumentosEntregue(true);
                })->whereDoesntHave('Admissao')->whereDoesntHave('Demissao')->orWhereHas('Admissao', function ($q) {
                    $q->whereIn('status', [Admissao::STATUS_ADMISSAO_PENDENTEDOCUMENTO]);
                });
            }
            if ($request->status == 'admitidos') {
                $resultado->whereHas('ResultadoIntegrado', function ($q) {
                    $q->whereDocumentosEntregue(true);
                })->whereHas('Admissao', function ($q) {
                    $q->where('status', Admissao::STATUS_ADMISSAO_ADMITIDO);
                })->whereDoesntHave('Demissao');
            }
            if ($request->status == 'demitidos') {
                $resultado->whereHas('ResultadoIntegrado', function ($q) {
                    $q->whereDocumentosEntregue(true);
                })->demitidos();
            }
        }

        if ($request->filled('campoBusca')) {
            $resultado->whereHas('Curriculo', function ($query) use ($request) {
                $query->where('nome', 'like', '%' . $request->campoBusca . '%')
                    ->orWhere('cpf', 'like', '%' . $request->campoBusca . '%')
                    ->orWhere('id', $request->campoBusca);
            });
        }

        if ($request->filled('campoCPF')) {
            $resultado->whereHas('Curriculo', function ($query) use ($request) {
                $query->whereCpf($request->campoBusca);
            });
        }

        if ($request->filled('campoVaga')) {
            $resultado->whereHas('VagaAberta', function ($query) use ($request) {
                $query->whereId($request->campoVaga);
            });
        }

        $resultado = $resultado->orderByDesc('created_at')->paginate($request->pages);

        $items = collect($resultado->items())->transform(function ($item) {
            $docs_curriculo_pre_adm = DocumentosCurriculosAdmissaoEmpresa::getDocumentoCurriculoAdmissaoEmpresa(auth()->user()->empresa_id)
                ->transform(function ($doc) use ($item) {
                    $doc->docs_curriculo_anexos = DB::table('documentos_curriculos')
                        ->whereTipo($doc->tipo)
                        ->where('curriculo_id', $item->curriculo_id)
                        ->join('arquivos', 'arquivos.id', '=', 'documentos_curriculos.arquivo_id')
                        ->get()->transform(function ($doc) {
                            $doc->url = "";
                            $doc->url_download = "";
                            if (in_array($doc->disco, Arquivo::LISTAGEM_DISCOS)) {
                                $doc->url = config('filesystems.disks.' . $doc->disco . '.urlShow') . "/{$doc->file}";
                                $doc->url_download = config('filesystemzs.disks.' . $doc->disco . '.urlDownload') . "/{$doc->file}";
                            };
                            return $doc;
                        });
                    $doc->qnt_anexos = count($doc->docs_curriculo_anexos);
                    return $doc;
                });
            $item->docs_curriculo_pre_adm = $docs_curriculo_pre_adm;
            $item->qnt_anexos = $docs_curriculo_pre_adm->sum('qnt_anexos');
            return $item;
        });

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => ['items' => $items, 'usuario_cliente_id' => auth()->user()->cliente_id, 'email_padrao' => Sistema::EMAILPADRAO]
        ]);
    }

    public function edit(FeedbackCurriculo $feedback)
    {
        return $feedback->load('Curriculo.Pessoa');
    }

    public function enviarEmail(Request $request)
    {
        $dados = $request->input();

        if ($dados['email'] == Sistema::EMAILPADRAO) {
            return response()->json([
                'msg' => 'O e-mail não pode ser ' . Sistema::EMAILPADRAO
            ], 400);
        }

        $dadosValidados = \Validator::make($dados, [
            'email' => 'required|email:rfc,dns',
        ]);
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Atualizar Email',
                'erros' => $dadosValidados->errors()
            ], 400);
        }
        try {
            DB::beginTransaction();
            $feedback = FeedbackCurriculo::whereId($dados['id'])->first();
            $feedback['email'] = $dados['email'];
            $curriculo = Curriculo::whereId($dados['curriculo_id'])->with('Pessoa')->first();
            $email_atual = $curriculo->email == $dados['email'];
            $email_padrao = $curriculo->email == Sistema::EMAILPADRAO;

            if (!$email_atual) {
                $curriculo->update(['email' => $dados['email']]);
                $curriculo->Pessoa->update(['login' => $dados['email']]);
            }

            $curriculo->EmailsPreAdmissao()->create([
                'observacao' => $dados['observacao'],
                'email_atual' => $email_atual,
                'email_padrao' => $email_padrao
            ]);

            DB::commit();
            JobEnvioDocumento::dispatch([
                'nome' => $curriculo->nome,
                'email' => $feedback['email'],
                'empresa_id' => $feedback->empresa_id,
                'url_documento' => env('APP_URL') . "/" . auth()->user()->Empresa->apelido . "/documentos",
            ]);
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['msg' => 'Erro ao enviar e-mail', 'erros' => $e->getMessage()], 400);
        }
    }
}
