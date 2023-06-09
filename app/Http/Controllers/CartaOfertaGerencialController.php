<?php

namespace App\Http\Controllers;

use App\Classes\ZapNotificacao;
use App\Http\Controllers\Api\IntegraSgiMybpController;
use App\Jobs\Entrevista\JobEnvioDocumento;
use App\Models\CartaOferta;
use App\Models\Sistema;
use App\Models\TelefoneCurriculo;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use MasterTag\DataHora;

class CartaOfertaGerencialController extends Controller
{
    public function index()
    {
        return view('g.admissao.documentos.cartaoferta.index');
    }

    public function filtro(Request $request)
    {
        $query = CartaOferta::with(
            'curriculo:id,nome,nascimento,rg,orgao_expeditor',
            'vagaProjeto.Projeto:id,nome',
            'vagaAberta.Cargo',
            'anexo'
        )->orderBy('updated_at');

        if (isset($request['status']) && $request['status'] != '') {
            $query->where('status', $request['status']);
        }

        if (isset($request['curriculo_id']) && $request['curriculo_id'] != '') {
            $query->where('curriculo_id', $request['curriculo_id']);
        }

        if (isset($request['vaga_projeto_id']) && $request['vaga_projeto_id'] != '') {
            $query->where('vaga_projeto_id', $request['vaga_projeto_id']);
        }

        if (isset($request['vagas_abertas_id']) && $request['vagas_abertas_id'] != '') {
            $query->where('vagas_abertas_id', $request['vagas_abertas_id']);
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

                if ($cartaOferta->local == CartaOferta::LOCAL_SGI) {
                    $dadosIntegra = $this->requestSgi($cartaOferta->token);
                    IntegraSgiMybpController::integra($dadosIntegra);
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
                return response()->json(['message' => $msg], 422);
            }

        }

        return response()->json(['message' => 'Nao encontrado'], 404);

    }

    public function atualizar(Request $request)
    {
        $resultado = $this->filtro($request)->paginate($request['pages']);
        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $resultado->items(),
                'lista_status' => CartaOferta::STATUS,
            ]
        ]);
    }

    public function requestSgi($token)
    {
        $client = new Client(['verify' => false, 'http_errors' => false]);
        $headers = [
            'X-API-TOKEN' => 'gTyF2ErmclLMRjzxBHo20OoXVqNhgnDKqCtQVRtsrfF1sOU4s6wK',
            'Content-Type' => 'application/json',
            'User-Agent' => 'MyBP'
        ];

        switch (env('APP_URL')) {
            case 'https://sgi.bpse.com.br':
                $url = 'https://sgi.bpse.com.br';
                break;
            case 'https://qasgi.bpse.com.br':
                $url = 'https://qasgi.bpse.com.br';
                break;
            default:
                $url = 'http://localhost:8884';
                break;
        }

        $response = $client->post("$url/api/carta-oferta/$token/integramybp", [
            'headers' => $headers,
        ]);

//        print_r($response->getBody()->getContents());

        return $response->getBody()->getContents();
    }

}
