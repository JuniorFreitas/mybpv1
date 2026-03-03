<?php

namespace App\Http\Controllers;

use App\Jobs\AssinaturaDigital\JobProcessarEnvioAssinatura;
use App\Models\Admissao;
use App\Models\Arquivo;
use App\Models\Cliente;
use App\Models\Curriculo;
use App\Models\DocumentoParaAssinatura;
use App\Models\EmpresaTemporaria;
use App\Models\FeedbackCurriculo;
use App\Models\LogHistorico;
use App\Models\Sistema;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use MasterTag\DataHora;
use PDF;
use Illuminate\Support\Str;

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
            array_merge(
                $this->listaDeRelacionamentos(),
                ['Curriculo:id,nome,email,cpf', 'Curriculo.User:id,login']
            )
        )->first();

        // Remove o prefixo 'get_documento_relacionado_' das chaves do objeto $feedback
        $formattedFeedback = [];
        foreach ($feedback->toArray() as $key => $value) {
            $formattedKey = preg_replace('/^get_documento_relacionado_/', '', $key);
            $formattedFeedback[$formattedKey] = $value;
        }

        $tipoModelosAssinatura = [
            'contratotrabalhoassinado',
            'termoconfiabilidade',
            'valetransporte',
            'acordocompensacaohoras',
            'termosalariofamilia',
            'declaracaodependentesimposto',
        ];
        $tiposDocumento = array_map(fn ($tipoModelo) => self::tipoModeloParaTipoDocumento($tipoModelo), $tipoModelosAssinatura);
        $docs = DocumentoParaAssinatura::withoutGlobalScopes()
            ->select(['id', 'token', 'status', 'arquivo_assinado_id', 'tipo_documento', 'documentable_id'])
            ->where('empresa_id', auth()->user()->empresa_id)
            ->where('documentable_type', FeedbackCurriculo::class)
            ->where('documentable_id', $feedback_id)
            ->whereIn('tipo_documento', $tiposDocumento)
            ->orderBy('id', 'desc')
            ->get();

        $documentosParaAssinatura = [];
        foreach ($docs as $doc) {
            $tipoModelo = self::tipoDocumentoParaTipoModelo($doc->tipo_documento);
            if (!isset($documentosParaAssinatura[$tipoModelo])) {
                $documentosParaAssinatura[$tipoModelo] = [
                    'id' => $doc->id,
                    'token' => $doc->token,
                    'status' => $doc->status,
                    'arquivo_assinado_id' => $doc->arquivo_assinado_id,
                    'tipo_documento' => $doc->tipo_documento,
                ];
            }
        }

        return [
            'dossie' => $formattedFeedback,
            'relacionamentos' => $this->listaRelacionamentoFormatada(),
            'documentos_para_assinatura' => $documentosParaAssinatura,
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
        $deleteKey = $type . 'Del';
        $relacionamento = "getDocumentoRelacionado" . \Str::studly($type);
        $label = strtoupper(str_replace('_', ' ', $type));
        $tipo = \Str::studly($type);

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
                Arquivo::whereId($anexo['id'])->update(['nome' => $anexo['nome']]);
                $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                if ($arquivo) {
                    $this->attachFileToFeedback($feedback, $relacionamento, $tipo, $label, $arquivo);
                }
            }
        }
    }

    // Método privado para anexar arquivo ao feedback e registrar no log
    private function attachFileToFeedback($feedback, $relacionamento, $tipo, $label, $arquivo)
    {
        $arquivo->temporario = false;
        $arquivo->chave = '';
        $arquivo->save();

        $feedback->$relacionamento()->attach($arquivo->id, [
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
                $view = "pdf.historico.dossie.customizado.{$cliente->apelido}.contratos.{$tipo_modelo}";
                if (view()->exists($view)) {
                    $pdf = \PDF::loadView($view, compact('dados', 'cliente'));
                } else {
                    $pdf = \PDF::loadView('pdf.historico.dossie.default.contratos.' . $tipo_modelo, compact('dados', 'cliente'));
                }
            }
        } else {
            $pdf = \PDF::loadView('pdf.historico.dossie.' . $tipo_modelo, compact('dados', 'cliente'));
        }

        $pdf->setPaper('A4');

        return $pdf->stream($tipo_modelo . (new DataHora())->nomeUnico() . ".pdf");
    }

    private static function tipoModeloParaTipoDocumento(string $tipo_modelo): string
    {
        $map = [
            'contratotrabalhoassinado' => 'contrato_trabalho',
            'termoconfiabilidade' => 'termo_confidencialidade',
            'valetransporte' => 'opcao_vale_transporte',
            'acordocompensacaohoras' => 'acordo_compensacao_horas',
            'termosalariofamilia' => 'termo_salario_familia',
            'declaracaodependentesimposto' => 'declaracao_dependentes_ir',
        ];
        return $map[$tipo_modelo] ?? $tipo_modelo;
    }

    private static function tipoDocumentoParaTipoModelo(string $tipo_documento): string
    {
        $map = [
            'contrato_trabalho' => 'contratotrabalhoassinado',
            'termo_confidencialidade' => 'termoconfiabilidade',
            'opcao_vale_transporte' => 'valetransporte',
            'acordo_compensacao_horas' => 'acordocompensacaohoras',
            'termo_salario_familia' => 'termosalariofamilia',
            'declaracao_dependentes_ir' => 'declaracaodependentesimposto',
        ];
        return $map[$tipo_documento] ?? $tipo_documento;
    }

    /**
     * Envia documento do dossiê para assinatura digital.
     * Fluxo assíncrono: valida e enfileira job para gerar PDF/criar envio em background.
     */
    public function enviarParaAssinatura(Request $request)
    {
        $request->validate([
            'tipo_modelo' => 'required|string|in:contratotrabalhoassinado,termoconfiabilidade,valetransporte,acordocompensacaohoras,termosalariofamilia,declaracaodependentesimposto',
            'curriculo_id' => 'required|integer',
            'feedback_id' => 'required|integer',
            'signatarios' => 'required|array|min:1',
            'signatarios.*.email' => 'required|email',
            'signatarios.*.nome' => 'required|string|max:255',
            'signatarios.*.cpf' => 'nullable|string|max:14',
            'signatarios.*.user_id' => 'nullable|exists:users,id',
        ]);

        $colaborador = FeedbackCurriculo::with(['Curriculo', 'Admissao'])->whereCurriculoId($request->curriculo_id)->whereId($request->feedback_id)->first();
        if (!$colaborador || $colaborador->empresa_id != auth()->user()->empresa_id) {
            return response()->json(['success' => false, 'message' => 'Registro não encontrado.'], 404);
        }
        if (!$colaborador->Admissao) {
            return response()->json(['success' => false, 'message' => 'É necessário existir admissão vinculada ao colaborador para enviar documento para assinatura.'], 400);
        }
        if (!$colaborador->Curriculo) {
            return response()->json(['success' => false, 'message' => 'Dados do currículo não encontrados.'], 400);
        }

        $empresaId = $colaborador->empresa_id;
        try {
            app(\App\Services\AssinaturaDigital\AssinaturaCotaService::class)->validarDisponibilidadeOrFail($empresaId);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
        $tipo_modelo = $request->tipo_modelo;
        JobProcessarEnvioAssinatura::dispatch(
            JobProcessarEnvioAssinatura::TIPO_DOSSIE,
            $empresaId,
            auth()->id(),
            [
                'tipo_modelo' => $tipo_modelo,
                'curriculo_id' => (int) $request->curriculo_id,
                'feedback_id' => (int) $request->feedback_id,
            ],
            $request->signatarios
        );

        \Log::info('AssinaturaDigital [dossie]: envio enfileirado', ['feedback_id' => $request->feedback_id, 'tipo' => $tipo_modelo]);

        return response()->json([
            'success' => true,
            'message' => 'Solicitação recebida. O documento será processado e enviado para assinatura.',
        ], 202);
    }
}
