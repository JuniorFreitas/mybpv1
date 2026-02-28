<?php

namespace App\Http\Controllers;

use App\Classes\ZapNotificacao;
use App\Http\Controllers\Api\IntegraSgiMybpController;
use App\Jobs\AssinaturaDigital\JobProcessarEnvioAssinatura;
use App\Jobs\Entrevista\JobEnvioDocumento;
use App\Models\CartaOferta;
use App\Models\DocumentoParaAssinatura;
use App\Models\Projeto;
use App\Models\Sistema;
use App\Models\TelefoneCurriculo;
use App\Models\User;
use App\Models\VagaProjeto;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use MasterTag\DataHora;

class CartaOfertaGerencialController extends Controller
{
    public function index()
    {
        return view('g.admissao.documentos.cartaoferta.index');
    }

    /**
     * Envia Carta Oferta para assinatura digital (signatário = candidato).
     */
    public function enviarParaAssinatura(Request $request)
    {
        $request->validate([
            'carta_oferta_id' => 'required|integer',
        ]);

        $cartaOferta = CartaOferta::where('id', $request->carta_oferta_id)
            ->where('empresa_id', auth()->user()->empresa_id)
            ->with([
                'Curriculo:id,nome,email,cpf',
                'empresa:id,razao_social,nome_fantasia',
                'vagaAberta:id,vaga_id',
                'vagaAberta.Vaga:id,nome',
                'vagaProjeto:id,vaga_aberta_id',
                'vagaProjeto.VagaAberta:id,vaga_id',
                'vagaProjeto.VagaAberta.Vaga:id,nome',
            ])
            ->first();

        if (!$cartaOferta || !$cartaOferta->Curriculo) {
            return response()->json(['success' => false, 'message' => 'Carta oferta não encontrada.'], 404);
        }
        if ($cartaOferta->status !== CartaOferta::STATUS_PENDENTE_ANEXO) {
            return response()->json(['success' => false, 'message' => 'Esta carta oferta já foi enviada ou não está pendente de anexo.'], 422);
        }

        $nomeCargo = 'Cargo';
        if ($cartaOferta->vagaAberta && $cartaOferta->vagaAberta->Vaga) {
            $nomeCargo = $cartaOferta->vagaAberta->Vaga->nome;
        } elseif ($cartaOferta->vagaProjeto && $cartaOferta->vagaProjeto->VagaAberta && $cartaOferta->vagaProjeto->VagaAberta->Vaga) {
            $nomeCargo = $cartaOferta->vagaProjeto->VagaAberta->Vaga->nome;
        }

        $empresaId = auth()->user()->empresa_id;
        if (empty($cartaOferta->Curriculo->email)) {
            return response()->json(['success' => false, 'message' => 'O candidato não possui e-mail cadastrado.'], 422);
        }

        JobProcessarEnvioAssinatura::dispatch(
            JobProcessarEnvioAssinatura::TIPO_CARTA_OFERTA,
            $empresaId,
            auth()->id(),
            ['carta_oferta_id' => (int) $cartaOferta->id],
            []
        );

        return response()->json([
            'success' => true,
            'message' => 'Solicitação recebida. A carta oferta será processada e enviada para assinatura em background.',
        ], 202);
    }

    public function filtro(Request $request)
    {
        $filtroIntervalo = $request->filtroPeriodo == 'true';

        $query = CartaOferta::with(
            'Curriculo:id,nome,nascimento,rg,orgao_expeditor,email',
            'Curriculo.TelPrincipal:id,tipo,pais,numero,detalhe,curriculo_id,principal',
            'vagaProjeto.Projeto:id,nome',
            'vagaAberta.Cargo',
            'Anexo'
        )->join('curriculos as c', 'curriculo_carta_oferta.curriculo_id', '=', 'c.id');

        if ($request['order'] == 'nome') {
            $query->orderBy('c.nome');
        } else {
            $query->orderBy('curriculo_carta_oferta.updated_at', 'desc');
        }

        if (isset($request['status']) && $request['status'] != '') {
            $query->where('status', $request['status']);
        }

        if ($filtroIntervalo) {
            $periodo = explode(' até ', $request->periodo);
            $dataInicio = new DataHora($periodo[0] . ' 00:00:00');
            $dataFim = new DataHora($periodo[1] . ' 23:59:59');
            $query->where('curriculo_carta_oferta.updated_at', '>=', $dataInicio->dataHoraInsert())
                ->where('curriculo_carta_oferta.updated_at', '<=', $dataFim->dataHoraInsert());
        }

        if (isset($request['campoBusca']) && $request['campoBusca'] != '') {
            $query->where('c.nome', 'like', '%' . $request['campoBusca'] . '%')
                ->orWhere('c.cpf', 'like', '%' . $request['campoBusca'] . '%');
        }

        if (isset($request['projeto_id']) && $request['projeto_id'] != '') {
            $query->whereHas('vagaProjeto.Projeto', function ($query) use ($request) {
                $query->where('projeto_id', $request['projeto_id']);
            });
        }

        if (isset($request['curriculo_id']) && $request['curriculo_id'] != '') {
            $query->where('curriculo_id', $request['curriculo_id']);
        }

        if (isset($request['vaga_projeto_id']) && $request['vaga_projeto_id'] != '') {
            $query->where('vaga_projeto_id', $request['vaga_projeto_id']);
        }

        return $query;
    }

    public function responder(Request $request)
    {

        $cartaOferta = CartaOferta::where('token', $request['token'])->first();

        if (!$cartaOferta) {
            return response()->json(['message' => 'Carta de oferta não encontrada!'], 404);
        }

        if ($cartaOferta->status == CartaOferta::STATUS_EXPIRADO) {
            return response()->json(['message' => 'Carta de oferta expirada'], 422);
        }

        if ($cartaOferta->status == CartaOferta::STATUS_AGUARDANDO_RH) {

            try {
                \DB::beginTransaction();
                $logs = $cartaOferta->first()->logs;

                $logs[] = [
                    'data' => (new DataHora())->dataHoraInsert(),
                    'mensagem' => "Carta de oferta {$request->resposta}",
                    'status' => $request->resposta,
                    'usuario' => auth()->user()->nome
                ];

                $cartaOferta->update([
                    'status' => $request->resposta,
                    'logs' => $logs
                ]);

                if ($cartaOferta->local == CartaOferta::LOCAL_SGI && $request->resposta == CartaOferta::STATUS_ACEITO_RH) {
                    IntegraSgiMybpController::integra($request->integraMybp);
                }

                \DB::commit();

                $telefone = $cartaOferta->curriculo->Telefones()->where('tipo', TelefoneCurriculo::TIPO_WHATS)->wherePrincipal(true)->first();
                $urlDocumentos = env('APP_URL') . "/" . auth()->user()->Empresa->apelido . "/documentos";

                if ($request->resposta == CartaOferta::STATUS_ACEITO_RH) {
                    $checklistArquivo = CartaOferta::checklistArquivo(auth()->user()->Empresa->apelido);
                    JobEnvioDocumento::dispatch([
                        'nome' => $cartaOferta->Curriculo->nome,
                        'email' => $cartaOferta->Curriculo->email,
                        'empresa_id' => $cartaOferta->empresa_id,
                        'url_documento' => $urlDocumentos,
                        'url_checklist' => route('download-checklist', ['empresa' => auth()->user()->Empresa->apelido]),
                    ]);

                    if ($telefone->tipo == TelefoneCurriculo::TIPO_WHATS) {
                        $dados['telefone'] = $telefone->sonumero;

                        $ambiente = env('AMBIENTE', 'local') == 'prod' ?: 'local';
                        if ($ambiente != 'prod') {
                            $zapTelAtivo = \DB::table("zap_numeros")->where("ativo", true)->first();
                            $dados['telefone'] = $zapTelAtivo ? $zapTelAtivo->telefone : '559899023762';
                        }

                        $mensagemWhats = "Prezado(a) sr(a), " . $cartaOferta->curriculo->nome . ", tudo bem?";
                        $mensagemWhats .= "\n\nParabéns por chegado até esta etapa! Você foi aprovado(a) na etapa de entrevista e seleção e agora vamos para a etapa de documentos para admissão.";
                        $mensagemWhats .= "\n\nEstamos enviando em anexo o PDF do checklist.";
                        $mensagemWhats .= "\n\nPara continuidade no processo, segue o link abaixo para que seja anexado os documentos conforme descrição.";
                        $mensagemWhats .= "\n\n" . $urlDocumentos;
                        $mensagemWhats .= "\n\nDestaca-se que é muito importante que todos os documentos sejam anexados corretamente e sem omissões para que não haja atraso na etapa de documentação, necessária para a continuidade de sua admissão";
                        $mensagemWhats .= "\n\nAtenciosamente,\n";
                        $mensagemWhats .= "*Time Recrutamento e Seleção BPSE*\n";

                        (new ZapNotificacao())->enviar([
                            'enviado_id' => auth()->id(),
                            'telefone' => preg_replace('/[^0-9]/', '', $dados['telefone']),
                            'mensagem' => $mensagemWhats,
                            "anexo" => [
                                "arquivo" => $checklistArquivo,
                                "tipo" => "pdf"
                            ]
                        ]);
                    }
                }

                return response()->json(['message' => 'Resposta computada com successo!'], 201);

            } catch (\Exception $e) {
                \DB::rollBack();
                $msg = "Erro ao integrar: {$e->getFile()} , {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
                \Log::debug($msg);
                Sistema::LogFormatado($request->input());
                return response()->json(['message' => $msg], 400);
            }

        }

        return response()->json(['message' => 'Nao encontrado'], 404);

    }

    public function atualizar(Request $request)
    {
        $resultado = $this->filtro($request)->paginate($request['pages']);
        $itens = $resultado->getCollection();
        $cartaIds = $itens->pluck('id')->filter()->values()->all();
        $docsByCartaId = [];
        if (!empty($cartaIds)) {
            $docs = DocumentoParaAssinatura::withoutGlobalScopes()
                ->select(['id', 'token', 'status', 'arquivo_assinado_id', 'tipo_documento', 'documentable_id'])
                ->where('empresa_id', auth()->user()->empresa_id)
                ->where('documentable_type', CartaOferta::class)
                ->whereIn('documentable_id', $cartaIds)
                ->orderBy('id', 'desc')
                ->get();

            foreach ($docs as $doc) {
                if (!isset($docsByCartaId[$doc->documentable_id])) {
                    $docsByCartaId[$doc->documentable_id] = [
                        'id' => $doc->id,
                        'token' => $doc->token,
                        'status' => $doc->status,
                        'arquivo_assinado_id' => $doc->arquivo_assinado_id,
                        'tipo_documento' => $doc->tipo_documento,
                    ];
                }
            }
        }

        $itens = $itens->map(function ($item) use ($docsByCartaId) {
            $item->documento_para_assinatura = $docsByCartaId[$item->id] ?? null;
            return $item;
        })->values();

        $vagasProjeto = Projeto::
        select(['id', 'nome', 'preenchidas', 'empresa_id', 'qnt_total', 'preenchidas'])->with(
            'VagasProjeto',
            'VagasProjeto.VagaAberta:id,empresa_id,vaga_id,titulo,municipio_id,ativo,ativo_sistema'
        )->orderBy('nome')->get();

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $itens,
                'lista_status' => CartaOferta::STATUS,
                'lista_projetos' => $vagasProjeto,
            ]
        ]);
    }

    public function requestSgi($token)
    {

        switch (env('APP_URL')) {
            case 'https://sistema.mybp.com.br':
                $url = 'https://sgi.bpse.com.br';
                break;
            case 'https://qa.mybp.com.br':
                $url = 'https://qasgi.bpse.com.br';
                break;
            default:
                $url = 'http://192.168.1.10:8884';
                break;
        }

        $endpoint = "$url/api/carta-oferta/$token/integramybp";

        $client = new Client();
        $headers = [
            'X-API-TOKEN' => 'gTyF2ErmclLMRjzxBHo20OoXVqNhgnDKqCtQVRtsrfF1sOU4s6wK',
            'Content-Type' => 'application/json',
            'User-Agent' => 'MyBP'
        ];

        $response = $client->post($endpoint, [
            'headers' => $headers,
        ]);


        return $response->getBody()->getContents();
    }

}
