<?php

namespace App\Http\Controllers;

use App\Models\Admissao;
use App\Models\Arquivo;
use App\Models\Cliente;
use App\Models\Curriculo;
use App\Models\EmpresaTemporaria;
use App\Models\FeedbackCurriculo;
use App\Models\LogHistorico;
use App\Models\User;
use Barryvdh\DomPDF\PDF;
use DB;
use Illuminate\Http\Request;
use MasterTag\DataHora;

class DossieController extends Controller
{

    public function show(Request $request, $feedback)
    {
        $feedback_id = $feedback;

        $feedback = FeedbackCurriculo::select('id', 'curriculo_id')->whereId($feedback_id)->with(
            'DocSelecao',
            'DocChecklist',
            'FichaRegistrada',
            'ContratoTrabalhoAssinado',
            'TermoConfiabilidade',
            'ValeTransporteAssinado',
            'AcordoHora',
            'SalarioFamiliaAssinado',
            'DeclaracaoDependentesImposto',
            'ComprovanteDevCtp',
            'OrdemServicoAssinada',
            'CertificadoTreinSeg',
            'FichaEntregaEpi',
            'ContraChequeMensais',
            'CartoesPonto',
            'AvisoFerias',
            'ControleAsos',
            'BookRescisao',
            'TermoRescisao',
            'GuiaSeguroDesemprego',
            'ChaveFgts',
            'ComprovantePagamento',
            'ExameDemissional',
            'NadaConstaFichaEpi',
            'ComprovanteDevolucaoCtps',
            'PppAssinado',
            'PlanoSaudeAssinado',
            'ArquivamentoEletronico',
            'ArquivamentoDossie'
        )->first();

        return $feedback;
    }

    public function store(Request $request, $feedback)
    {

        $feedback = FeedbackCurriculo::whereId($feedback)->first();

        $dados = $request->input();
        $dadosValidados = \Validator::make($dados, [

        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Salvar Informações',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();

                //Remove DocSelecao
                if (isset($dados['doc_selecaoDel'])) {
                    foreach ($dados['doc_selecaoDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();
                        LogHistorico::createLog($feedback->id, 'Removeu Documento de Seleção');
                    }
                }
                // inseri uma nova DocSelecao
                if (isset($dados['doc_selecao'])) {
                    foreach ($dados['doc_selecao'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();

                            $feedback->DocSelecao()->attach($arquivo->id, [
                                'curriculo_id' => $feedback->curriculo_id,
                                'tipo' => 'DocSelecao',
                                'label' => 'DOCUMENTO DE SELEÇÃO'
                            ]);

                            LogHistorico::createLog($feedback->id, 'Inseriu Documento de Seleção');
                        }
                    }
                }

                //Remove DocChecklist
                if (isset($dados['doc_checklistDel'])) {
                    foreach ($dados['doc_checklistDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();
                        LogHistorico::createLog($feedback->id, 'Removeu Documentos de Checklist Admissão');
                    }
                }
                // inseri uma nova DocChecklist
                if (isset($dados['doc_checklist'])) {
                    foreach ($dados['doc_checklist'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $feedback->DocChecklist()->attach($arquivo->id, [
                                'curriculo_id' => $feedback->curriculo_id,
                                'tipo' => 'DocChecklist',
                                'label' => 'DOCUMENTOS CHECK LIST ADMISSÃO'
                            ]);

                            LogHistorico::createLog($feedback->id, 'Inseriu Documentos de Checklist Admissão');
                        }
                    }
                }

                //Remove FichaRegistrada
                if (isset($dados['ficha_registradaDel'])) {
                    foreach ($dados['ficha_registradaDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();

                        LogHistorico::createLog($feedback->id, 'Removeu Ficha Registro Assinada');
                    }
                }
                // insere uma nova Ficha Registrada
                if (isset($dados['ficha_registrada'])) {
                    foreach ($dados['ficha_registrada'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $feedback->FichaRegistrada()->attach($arquivo->id, [
                                'curriculo_id' => $feedback->curriculo_id,
                                'tipo' => 'FichaRegistrada',
                                'label' => 'FICHA REGISTRO ASSINADA'
                            ]);

                            LogHistorico::createLog($feedback->id, 'Inseriu Ficha Registro Assinada');
                        }
                    }
                }

                //Remove ContratoTrabalhoAssinado
                if (isset($dados['contrato_trabalho_assinadoDel'])) {
                    foreach ($dados['contrato_trabalho_assinadoDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();

                        LogHistorico::createLog($feedback->id, 'Removeu Contrato de Trabalho Assinado');
                    }
                }
                // inseri uma nova ContratoTrabalhoAssinado
                if (isset($dados['contrato_trabalho_assinado'])) {
                    foreach ($dados['contrato_trabalho_assinado'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $feedback->ContratoTrabalhoAssinado()->attach($arquivo->id, [
                                'curriculo_id' => $feedback->curriculo_id,
                                'tipo' => 'ContratoTrabalhoAssinado',
                                'label' => 'CONTRATO DE TRABALHO ASSINADO'
                            ]);

                            LogHistorico::createLog($feedback->id, 'Inseriu Contrato de Trabalho Assinado');
                        }
                    }
                }

                //Remove TermoConfiabilidade
                if (isset($dados['termo_confiabilidadeDel'])) {
                    foreach ($dados['termo_confiabilidadeDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();

                        LogHistorico::createLog($feedback->id, 'Removeu Termo de Confidencialidade Assinado');
                    }
                }
                // inseri uma nova TermoConfiabilidade
                if (isset($dados['termo_confiabilidade'])) {
                    foreach ($dados['termo_confiabilidade'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $feedback->TermoConfiabilidade()->attach($arquivo->id, [
                                'curriculo_id' => $feedback->curriculo_id,
                                'tipo' => 'TermoConfiabilidade',
                                'label' => 'TERMO DE CONFIDENCIALIDADE ASSINADO'
                            ]);

                            LogHistorico::createLog($feedback->id, 'Inseriu Termo de Confidencialidade Assinado');
                        }
                    }
                }

                //Remove ValeTransporteAssinado
                if (isset($dados['vale_transporte_assinadoDel'])) {
                    foreach ($dados['vale_transporte_assinadoDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();

                        LogHistorico::createLog($feedback->id, 'Removeu Opção Vale Transporte Assinado');
                    }
                }
                // inseri uma nova ValeTransporteAssinado
                if (isset($dados['vale_transporte_assinado'])) {
                    foreach ($dados['vale_transporte_assinado'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $feedback->ValeTransporteAssinado()->attach($arquivo->id, [
                                'curriculo_id' => $feedback->curriculo_id,
                                'tipo' => 'ValeTransporteAssinado',
                                'label' => 'OPÇÃO VALE TRANSPORTE ASSINADO'
                            ]);

                            LogHistorico::createLog($feedback->id, 'Inseriu Opção Vale Transporte Assinado');
                        }
                    }
                }

                //Remove AcordoHora
                if (isset($dados['acordo_horaDel'])) {
                    foreach ($dados['acordo_horaDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();

                        LogHistorico::createLog($feedback->id, 'Removeu Acordo Compensação de Horas Assinado');
                    }
                }
                // inseri uma nova AcordoHora
                if (isset($dados['acordo_hora'])) {
                    foreach ($dados['acordo_hora'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $feedback->AcordoHora()->attach($arquivo->id, [
                                'curriculo_id' => $feedback->curriculo_id,
                                'tipo' => 'AcordoHora',
                                'label' => 'ACORDO COMPENSAÇÃO DE HORAS ASSINADO'
                            ]);

                            LogHistorico::createLog($feedback->id, 'Inseriu Acordo Compensação de Horas Assinado');
                        }
                    }
                }

                //Remove SalarioFamiliaAssinado
                if (isset($dados['salario_familia_assinadoDel'])) {
                    foreach ($dados['salario_familia_assinadoDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();

                        LogHistorico::createLog($feedback->id, 'Removeu Termo Salário Família Assinado');
                    }
                }
                // inseri uma nova SalarioFamiliaAssinado
                if (isset($dados['salario_familia_assinado'])) {
                    foreach ($dados['salario_familia_assinado'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $feedback->SalarioFamiliaAssinado()->attach($arquivo->id, [
                                'curriculo_id' => $feedback->curriculo_id,
                                'tipo' => 'SalarioFamiliaAssinado',
                                'label' => 'TERMO SALÁRIO FAMILIA ASSINADO'
                            ]);

                            LogHistorico::createLog($feedback->id, 'Inseriu Termo Salário Família Assinado');
                        }
                    }
                }

                //Remove DeclaracaoDependentesImposto
                if (isset($dados['declaracao_dependentes_impostoDel'])) {
                    foreach ($dados['declaracao_dependentes_impostoDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();

                        LogHistorico::createLog($feedback->id, 'Removeu Declaração Dependentes Imposto de Renda Assinado');
                    }
                }
                // inseri uma nova DeclaracaoDependentesImposto
                if (isset($dados['declaracao_dependentes_imposto'])) {
                    foreach ($dados['declaracao_dependentes_imposto'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $feedback->DeclaracaoDependentesImposto()->attach($arquivo->id, [
                                'curriculo_id' => $feedback->curriculo_id,
                                'tipo' => 'DeclaracaoDependentesImposto',
                                'label' => 'DECLARAÇÃO DEPENDENTES IMPOSTO DE RENDA ASSINADO'
                            ]);

                            LogHistorico::createLog($feedback->id, 'Inseriu Declaração Dependentes Imposto de Renda Assinado');
                        }
                    }
                }

                //Remove ComprovanteDevCtp
                if (isset($dados['comprovante_dev_ctpDel'])) {
                    foreach ($dados['comprovante_dev_ctpDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();

                        LogHistorico::createLog($feedback->id, 'Removeu Comprovante Devolução CTPS Assinado');
                    }
                }
                // inseri uma nova ComprovanteDevCtp
                if (isset($dados['comprovante_dev_ctp'])) {
                    foreach ($dados['comprovante_dev_ctp'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $feedback->ComprovanteDevCtp()->attach($arquivo->id, [
                                'curriculo_id' => $feedback->curriculo_id,
                                'tipo' => 'ComprovanteDevCtp',
                                'label' => 'COMPROVANTE DEVOLUÇÃO CTPS ASSINADO'
                            ]);

                            LogHistorico::createLog($feedback->id, 'Inseriu Comprovante Devolução CTPS Assinado');
                        }
                    }
                }

                //Remove OrdemServicoAssinada
                if (isset($dados['ordem_servico_assinadaDel'])) {
                    foreach ($dados['ordem_servico_assinadaDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();

                        LogHistorico::createLog($feedback->id, 'Removeu Ordem de Serviço Assinada');
                    }
                }
                // inseri uma nova OrdemServicoAssinada
                if (isset($dados['ordem_servico_assinada'])) {
                    foreach ($dados['ordem_servico_assinada'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $feedback->OrdemServicoAssinada()->attach($arquivo->id, [
                                'curriculo_id' => $feedback->curriculo_id,
                                'tipo' => 'OrdemServicoAssinada',
                                'label' => 'ORDEM DE SERVIÇO ASSINADA'
                            ]);

                            LogHistorico::createLog($feedback->id, 'Inseriu Ordem de Serviço Assinada');
                        }
                    }
                }

                //Remove CertificadoTreinSeg
                if (isset($dados['certificado_trein_segDel'])) {
                    foreach ($dados['certificado_trein_segDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();

                        LogHistorico::createLog($feedback->id, 'Removeu Certificados de Treinamentos Segurança');
                    }
                }
                // inseri uma nova CertificadoTreinSeg
                if (isset($dados['certificado_trein_seg'])) {
                    foreach ($dados['certificado_trein_seg'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $feedback->CertificadoTreinSeg()->attach($arquivo->id, [
                                'curriculo_id' => $feedback->curriculo_id,
                                'tipo' => 'CertificadoTreinSeg',
                                'label' => 'CERTIFICADOS DE TREINAMENTOS SEGURANÇA'
                            ]);
                            LogHistorico::createLog($feedback->id, 'Inseriu Certificados de Treinamentos Segurança');
                        }
                    }
                }

                //Remove FichaEntregaEpi
                if (isset($dados['ficha_entrega_epiDel'])) {
                    foreach ($dados['ficha_entrega_epiDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();
                        LogHistorico::createLog($feedback->id, 'Removeu Ficha de Entrega de EPI');
                    }
                }
                // inseri uma nova FichaEntregaEpi
                if (isset($dados['ficha_entrega_epi'])) {
                    foreach ($dados['ficha_entrega_epi'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $feedback->FichaEntregaEpi()->attach($arquivo->id, [
                                'curriculo_id' => $feedback->curriculo_id,
                                'tipo' => 'FichaEntregaEpi',
                                'label' => 'FICHA DE ENTREGA DE EPI'
                            ]);

                            LogHistorico::createLog($feedback->id, 'Inseriu Ficha de Entrega de EPI');
                        }
                    }
                }

                //Remove ContraChequeMensais
                if (isset($dados['contra_cheque_mensaisDel'])) {
                    foreach ($dados['contra_cheque_mensaisDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();

                        LogHistorico::createLog($feedback->id, 'Removeu Contracheques Mensais');
                    }
                }
                // inseri uma nova ContraChequeMensais
                if (isset($dados['contra_cheque_mensais'])) {
                    foreach ($dados['contra_cheque_mensais'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $feedback->ContraChequeMensais()->attach($arquivo->id, [
                                'curriculo_id' => $feedback->curriculo_id,
                                'tipo' => 'ContraChequeMensais',
                                'label' => 'CONTRACHEQUES MENSAIS'
                            ]);

                            LogHistorico::createLog($feedback->id, 'Inseriu Contracheques Mensais');
                        }
                    }
                }

                //Remove CartoesPonto
                if (isset($dados['cartoes_pontoDel'])) {
                    foreach ($dados['cartoes_pontoDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();

                        LogHistorico::createLog($feedback->id, 'Removeu Cartões de Ponto Mensais');
                    }
                }
                // inseri uma nova CartoesPonto
                if (isset($dados['cartoes_ponto'])) {
                    foreach ($dados['cartoes_ponto'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $feedback->CartoesPonto()->attach($arquivo->id, [
                                'curriculo_id' => $feedback->curriculo_id,
                                'tipo' => 'CartoesPonto',
                                'label' => 'CARTÕES DE PONTO MENSAIS'
                            ]);

                            LogHistorico::createLog($feedback->id, 'Inseriu Cartões de Ponto Mensais');
                        }
                    }
                }

                //Remove AvisoFerias
                if (isset($dados['aviso_feriasDel'])) {
                    foreach ($dados['aviso_feriasDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();

                        LogHistorico::createLog($feedback->id, 'Removeu Avisos de Férias Anuais');
                    }
                }
                // inseri uma nova AvisoFerias
                if (isset($dados['aviso_ferias'])) {
                    foreach ($dados['aviso_ferias'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $feedback->AvisoFerias()->attach($arquivo->id, [
                                'curriculo_id' => $feedback->curriculo_id,
                                'tipo' => 'AvisoFerias',
                                'label' => 'AVISOS DE FÉRIAS ANUAIS'
                            ]);

                            LogHistorico::createLog($feedback->id, 'Inseriu Avisos de Férias Anuais');
                        }
                    }
                }

                //Remove ControleAsos
                if (isset($dados['controle_asosDel'])) {
                    foreach ($dados['controle_asosDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();

                        LogHistorico::createLog($feedback->id, 'Removeu Pasta de Controle de ASOS');
                    }
                }
                // inseri uma nova ControleAsos
                if (isset($dados['controle_asos'])) {
                    foreach ($dados['controle_asos'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $feedback->ControleAsos()->attach($arquivo->id, [
                                'curriculo_id' => $feedback->curriculo_id,
                                'tipo' => 'ControleAsos',
                                'label' => 'PASTA DE CONTROLE DE ASOS: ADMISSIONAIS, PERIODICOS,MUDANÇA DE FUNÇÃO, DEMISSIONAL'
                            ]);

                            LogHistorico::createLog($feedback->id, 'Inseriu Pasta de Controle de ASOS');
                        }
                    }
                }

                //Remove BookRescisao
                if (isset($dados['book_rescisaoDel'])) {
                    foreach ($dados['book_rescisaoDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();

                        LogHistorico::createLog($feedback->id, 'Removeu Book de Rescisão - Checklist Demissão');
                    }
                }
                // inseri uma nova BookRescisao
                if (isset($dados['book_rescisao'])) {
                    foreach ($dados['book_rescisao'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $feedback->BookRescisao()->attach($arquivo->id, [
                                'curriculo_id' => $feedback->curriculo_id,
                                'tipo' => 'BookRescisao',
                                'label' => 'BOOK DE RESCISÃO – CHECK LIST DEMISSÃO'
                            ]);

                            LogHistorico::createLog($feedback->id, 'Inseriu Book de Rescisão - Checklist Demissão');
                        }
                    }
                }

                //Remove TermoRescisao
                if (isset($dados['termo_rescisaoDel'])) {
                    foreach ($dados['termo_rescisaoDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();

                        LogHistorico::createLog($feedback->id, 'Removeu Termo de Rescisão de Contrato de Trabalho Assinado');
                    }
                }
                // inseri uma nova TermoRescisao
                if (isset($dados['termo_rescisao'])) {
                    foreach ($dados['termo_rescisao'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $feedback->TermoRescisao()->attach($arquivo->id, [
                                'curriculo_id' => $feedback->curriculo_id,
                                'tipo' => 'TermoRescisao',
                                'label' => 'TERMO DE RESCISAO DE CONTRATO DE TRABALHO ASSINADO'
                            ]);

                            LogHistorico::createLog($feedback->id, 'Inseriu Termo de Rescisão de Contrato de Trabalho Assinado');
                        }
                    }
                }

                //Remove GuiaSeguroDesemprego
                if (isset($dados['guia_seguro_desempregoDel'])) {
                    foreach ($dados['guia_seguro_desempregoDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();

                        LogHistorico::createLog($feedback->id, 'Removeu Guias de Seguro Desemprego Assinadas');
                    }
                }
                // inseri uma nova GuiaSeguroDesemprego
                if (isset($dados['guia_seguro_desemprego'])) {
                    foreach ($dados['guia_seguro_desemprego'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $feedback->GuiaSeguroDesemprego()->attach($arquivo->id, [
                                'curriculo_id' => $feedback->curriculo_id,
                                'tipo' => 'GuiaSeguroDesemprego',
                                'label' => 'GUIAS DE SEGURO DESEMPREGO ASSINADAS'
                            ]);

                            LogHistorico::createLog($feedback->id, 'Inseriu Guias de Seguro Desemprego Assinadas');
                        }
                    }
                }

                //Remove ChaveFgts
                if (isset($dados['chave_fgtsDel'])) {
                    foreach ($dados['chave_fgtsDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();

                        LogHistorico::createLog($feedback->id, 'Removeu Chave de FGTS Assinada');
                    }
                }
                // inseri uma nova ChaveFgts
                if (isset($dados['chave_fgts'])) {
                    foreach ($dados['chave_fgts'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $feedback->ChaveFgts()->attach($arquivo->id, [
                                'curriculo_id' => $feedback->curriculo_id,
                                'tipo' => 'ChaveFgts',
                                'label' => 'CHAVE DE FGTS ASSINADA'
                            ]);

                            LogHistorico::createLog($feedback->id, 'Inseriu Chave de FGTS Assinada');
                        }
                    }
                }

                //Remove ComprovantePagamento
                if (isset($dados['comprovante_pagamentoDel'])) {
                    foreach ($dados['comprovante_pagamentoDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();

                        LogHistorico::createLog($feedback->id, 'Removeu Comprovante de Pagamento');
                    }
                }
                // inseri uma nova ComprovantePagamento
                if (isset($dados['comprovante_pagamento'])) {
                    foreach ($dados['comprovante_pagamento'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $feedback->ComprovantePagamento()->attach($arquivo->id, [
                                'curriculo_id' => $feedback->curriculo_id,
                                'tipo' => 'ComprovantePagamento',
                                'label' => 'COMPROVANTE DE PAGAMENTO'
                            ]);

                            LogHistorico::createLog($feedback->id, 'Inseriu Comprovante de Pagamento');
                        }
                    }
                }

                //Remove ExameDemissional
                if (isset($dados['exame_demissionalDel'])) {
                    foreach ($dados['exame_demissionalDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();

                        LogHistorico::createLog($feedback->id, 'Removeu Exame Demissional');
                    }
                }
                // inseri uma nova ExameDemissional
                if (isset($dados['exame_demissional'])) {
                    foreach ($dados['exame_demissional'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $feedback->ExameDemissional()->attach($arquivo->id, [
                                'curriculo_id' => $feedback->curriculo_id,
                                'tipo' => 'ExameDemissional',
                                'label' => 'EXAME DEMISSIONAL'
                            ]);

                            LogHistorico::createLog($feedback->id, 'Inseriu Exame Demissional');
                        }
                    }
                }

                //Remove NadaConstaFichaEpi
                if (isset($dados['nada_consta_ficha_epiDel'])) {
                    foreach ($dados['nada_consta_ficha_epiDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();

                        LogHistorico::createLog($feedback->id, 'Removeu Nada Consta de Baixa e Ficha de Entrega de EPI');
                    }
                }
                // inseri uma nova NadaConstaFichaEpi
                if (isset($dados['nada_consta_ficha_epi'])) {
                    foreach ($dados['nada_consta_ficha_epi'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $feedback->NadaConstaFichaEpi()->attach($arquivo->id, [
                                'curriculo_id' => $feedback->curriculo_id,
                                'tipo' => 'NadaConstaFichaEpi',
                                'label' => 'NADA CONSTA DE BAIXA E FICHA DE ENTREGA DE EPI'
                            ]);

                            LogHistorico::createLog($feedback->id, 'Inseriu Nada Consta de Baixa e Ficha de Entrega de EPI');
                        }
                    }
                }

                //Remove ComprovanteDevolucaoCtps
                if (isset($dados['comprovante_devolucao_ctpsDel'])) {
                    foreach ($dados['comprovante_devolucao_ctpsDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();

                        LogHistorico::createLog($feedback->id, 'Removeu Comprovante Devolução CTPS Assinado');
                    }
                }
                // inseri uma nova ComprovanteDevolucaoCtps
                if (isset($dados['comprovante_devolucao_ctps'])) {
                    foreach ($dados['comprovante_devolucao_ctps'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $feedback->ComprovanteDevolucaoCtps()->attach($arquivo->id, [
                                'curriculo_id' => $feedback->curriculo_id,
                                'tipo' => 'ComprovanteDevolucaoCtps',
                                'label' => 'COMPROVANTE DEVOLUÇÃO CTPS ASSINADO'
                            ]);

                            LogHistorico::createLog($feedback->id, 'Inseriu Comprovante Devolução CTPS Assinado');
                        }
                    }
                }

                //Remove PppAssinado
                if (isset($dados['ppp_assinadoDel'])) {
                    foreach ($dados['ppp_assinadoDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();

                        LogHistorico::createLog($feedback->id, 'Removeu PPP Assinado');
                    }
                }
                // inseri uma nova PppAssinado
                if (isset($dados['ppp_assinado'])) {
                    foreach ($dados['ppp_assinado'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $feedback->PppAssinado()->attach($arquivo->id, [
                                'curriculo_id' => $feedback->curriculo_id,
                                'tipo' => 'PppAssinado',
                                'label' => 'PPP ASSINADO'
                            ]);

                            LogHistorico::createLog($feedback->id, 'Inseriu PPP Assinado');
                        }
                    }
                }

                //Remove ArquivamentoEletronico
                if (isset($dados['arquivamento_eletronicoDel'])) {
                    foreach ($dados['arquivamento_eletronicoDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();

                        LogHistorico::createLog($feedback->id, 'Removeu Forma de Arquivamento Registro Eletrônico');
                    }
                }
                // inseri uma nova ArquivamentoEletronico
                if (isset($dados['arquivamento_eletronico'])) {
                    foreach ($dados['arquivamento_eletronico'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $feedback->ArquivamentoEletronico()->attach($arquivo->id, [
                                'curriculo_id' => $feedback->curriculo_id,
                                'tipo' => 'ArquivamentoEletronico',
                                'label' => 'FORMA DE ARQUIVAMENTO REGISTRO ELETRÔNICO'
                            ]);

                            LogHistorico::createLog($feedback->id, 'Inseriu Forma de Arquivamento Registro Eletrônico');
                        }
                    }
                }

                //Remove ArquivamentoDossie
                if (isset($dados['arquivamento_dossieDel'])) {
                    foreach ($dados['arquivamento_dossieDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();

                        LogHistorico::createLog($feedback->id, 'Removeu Forma de Arquivamento Dossiê');
                    }
                }
                // inseri uma nova ArquivamentoDossie
                if (isset($dados['arquivamento_dossie'])) {
                    foreach ($dados['arquivamento_dossie'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $feedback->ArquivamentoDossie()->attach($arquivo->id, [
                                'curriculo_id' => $feedback->curriculo_id,
                                'tipo' => 'ArquivamentoDossie',
                                'label' => 'FORMA DE ARQUIVAMENTO DOSSIÊ'
                            ]);

                            LogHistorico::createLog($feedback->id, 'Inseriu Forma de Arquivamento Dossiê');
                        }
                    }
                }

                //Remove PlanoSaudeAssinado
                if (isset($dados['plano_saude_assinadoDel'])) {
                    foreach ($dados['plano_saude_assinadoDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();

                        LogHistorico::createLog($feedback->id, 'Removeu Plano de Saúde Assinado');
                    }
                }
                // inseri uma nova PlanoSaudeAssinado
                if (isset($dados['plano_saude_assinado'])) {
                    foreach ($dados['plano_saude_assinado'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $feedback->PlanoSaudeAssinado()->attach($arquivo->id, [
                                'curriculo_id' => $feedback->curriculo_id,
                                'tipo' => 'PlanoSaudeAssinado',
                                'label' => 'PLANO DE SAÚDE ASSINADO'
                            ]);

                            LogHistorico::createLog($feedback->id, 'Inseriu Plano de Saúde Assinado');
                        }
                    }
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

    public function downloadModelo($tipo_modelo, $curriculo_id)
    {
        $dados = Curriculo::whereId($curriculo_id)->first();
        $cliente = Cliente::whereId($dados->User->empresa_id)->first();
        $tipo_admissao = \Str::slug($dados->FeedBack->Admissao->tipo_admissao);

        if ($tipo_modelo == 'contratotrabalhoassinado') {
            if (in_array($dados->FeedBack->Admissao->tipo_admissao, [Admissao::TIPO_ADMISSAO_TEMPORARIO, Admissao::TIPO_ADMISSAO_INTERMITENTE, Admissao::TIPO_ADMISSAO_DETERMINADO])) {
                $temporaria = EmpresaTemporaria::whereEmpresaId($dados->User->empresa_id)->first();
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
