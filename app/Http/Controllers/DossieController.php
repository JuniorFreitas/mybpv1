<?php

namespace App\Http\Controllers;

use App\Models\Admissao;
use App\Models\Arquivo;
use App\Models\Cliente;
use App\Models\Curriculo;
use App\Models\EmpresaTemporaria;
use App\Models\FeedbackCurriculo;
use App\Models\LogHistorico;
use App\Models\Sistema;
use App\Models\User;
use Barryvdh\DomPDF\PDF;
use DB;
use Illuminate\Http\Request;
use MasterTag\DataHora;

class DossieController extends Controller
{
    private function listaDeRelacionamentos()
    {
        $relacionamentos = [];
        foreach (FeedbackCurriculo::RELACIONAMENTOS_DOCS as $relacionamento) {
            $relacionamentos[] = 'getDocumentoRelacionado' . $relacionamento;
        }
        return $relacionamentos;
    }

    private function removePrefixNomeRelacionamento()
    {
        $relacionamentos = [];
        foreach (FeedbackCurriculo::RELACIONAMENTOS_DOCS as $relacionamento) {
            $relacionamentos[] = str_replace('getDocumentoRelacionado', '', $relacionamento);
        }
        return $relacionamentos;
    }

    protected function listaRelacionamentoFormatada()
    {
        $relacionamentos = [];
        foreach ($this->separeNomeRelacionamentoUnderline() as $relacionamento) {
            $relacionamentos[] = [
                'comum' => $relacionamento,
                'del' => $relacionamento . "Del"
            ];
        }

        return $relacionamentos;
    }

    private function separeNomeRelacionamentoUnderline()
    {
        $relacionamentos = [];
        foreach ($this->removePrefixNomeRelacionamento() as $relacionamento) {
            $relacionamentos[] = \Str::snake($relacionamento);
        }
        return $relacionamentos;
    }

    public function show(Request $request, $feedback)
    {
        $feedback_id = $feedback;
        $feedback = FeedbackCurriculo::select('id', 'curriculo_id')->whereId($feedback_id)->with(
            $this->listaDeRelacionamentos()
        )->first();

        // Remove o prefixo 'get_documento_relacionado_' das chaves do objeto $feedback
        $formattedFeedback = [];
        foreach ($feedback->toArray() as $key => $value) {
            $formattedKey = preg_replace('/^get_documento_relacionado_/', '', $key);
            $formattedFeedback[$formattedKey] = $value;
        }

        return [
            'dossie' => $formattedFeedback,
            'relacionamentos' => $this->listaRelacionamentoFormatada(),
        ];
    }

    public function store(Request $request, $feedback)
    {
        $feedback = FeedbackCurriculo::whereId($feedback)->first();
        $dados = $request->input();
        $dadosValidados = \Validator::make($dados, []);

        if ($dadosValidados->fails()) {
            return response()->json([
                'msg' => 'Erro ao Salvar Informações',
                'erros' => $dadosValidados->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();

            foreach ($this->separeNomeRelacionamentoUnderline() as $type) {
                $this->handleDocument($type, $dados, $feedback);
            }

            DB::commit();
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error EM SALVAR DOSSIE:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
            \Log::debug($msg);
            return response()->json(['msg' => $msg], 400);
        }
    }

    private function handleDocument($type, $dados, $feedback)
    {
        // Criação de variáveis locais
        $deleteKey = $type . 'Del';
        $label = strtoupper(str_replace('_', ' ', $type));
        $tipo = ucwords(str_replace('_', '', $type));

        // Remove documentos marcados para exclusão
        if (isset($dados[$deleteKey])) {
            foreach ($dados[$deleteKey] as $id_anexo) {
                $arquivo = Arquivo::find($id_anexo);
                if ($arquivo) {
                    $arquivo->excluir();
                    LogHistorico::createLog($feedback->id, "Removeu $label");
                }
            }
        }

        // Adiciona documentos
        if (isset($dados[$type])) {
            foreach ($dados[$type] as $anexo) {
                $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                if ($arquivo) {
                    $this->attachFileToFeedback($feedback, $tipo, $label, $arquivo);
                }
            }
        }
    }

    // Método privado para anexar arquivo ao feedback e registrar no log
    private function attachFileToFeedback($feedback, $tipo, $label, $arquivo)
    {
        $arquivo->temporario = false;
        $arquivo->chave = '';
        $arquivo->save();

        $feedback->$tipo()->attach($arquivo->id, [
            'curriculo_id' => $feedback->curriculo_id,
            'tipo' => $tipo,
            'label' => $label
        ]);

        LogHistorico::createLog($feedback->id, "Inseriu $label");
    }


    // Anexos-------------------------------------------------
    public function uploadAnexos(Request $request)
    {
        return Arquivo::uploadAnexos($request, Arquivo::MIMEAPENASIMAGENSPDF, Arquivo::DISCO_DOSSIE);
    }

    public function anexoShow(Request $request, $arquivo)
    {
        return Arquivo::anexoShow(Arquivo::DISCO_DOSSIE, $arquivo);
    }

    public function anexoDelete(Request $request, $arquivo)
    {
        return Arquivo::anexoDelete(Arquivo::DISCO_DOSSIE, $arquivo);
    }

    //anexo ou foto
    public function download(Request $request, $arquivo)
    {
        return Arquivo::anexoDownload(Arquivo::DISCO_DOSSIE, $arquivo);
    }

//    DOWNLOAD MODELOS DE IMPRESSÃO COM OS DADOS DOS FUNCIONÁRIOS

    public function downloadModelo($tipo_modelo, $curriculo_id, $feedback_id)
    {
        $colaborador = FeedbackCurriculo::whereCurriculoId($curriculo_id)->whereId($feedback_id)->first();

        $cliente = Cliente::whereId($colaborador->Curriculo->User->empresa_id)->first();
        $tipo_admissao = \Str::slug($colaborador->Admissao->tipo_admissao);

        $dados = [
            'dados_empresa' => Sistema::getEmpresaFilialMatriz($colaborador->Admissao->centro_custo_filial_id, $colaborador->empresa_id),
            'dados_colaborador' => $colaborador,
            'solicitante' => User::select('nome')->find(auth()->id())->nome
        ];

        if ($tipo_modelo == 'contratotrabalhoassinado') {
            if (in_array($colaborador->Admissao->tipo_admissao, [Admissao::TIPO_ADMISSAO_TEMPORARIO, Admissao::TIPO_ADMISSAO_INTERMITENTE, Admissao::TIPO_ADMISSAO_DETERMINADO])) {
                $temporaria = EmpresaTemporaria::whereEmpresaId($colaborador->empresa_id)->first();
                $pdf = \PDF::loadView('pdf.historico.dossie.contratos.' . $tipo_admissao, compact('dados', 'cliente', 'temporaria'));
            } else {
                $pdf = \PDF::loadView('pdf.historico.dossie.' . $tipo_modelo, compact('dados', 'cliente'));
            }
        } else {
            $pdf = \PDF::loadView('pdf.historico.dossie.' . $tipo_modelo, compact('dados', 'cliente'));
        }

        $pdf->setPaper('A4');

        return $pdf->stream($tipo_modelo . (new DataHora())->nomeUnico() . ".pdf");
    }


}
