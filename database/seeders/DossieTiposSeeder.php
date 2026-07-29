<?php

namespace Database\Seeders;

use App\Models\DossieTipo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DossieTiposSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tipos = [
            [
                'tipo' => 'DocSelecao',
                'chave' => 'doc_selecao',
                'label' => 'DOCUMENTO DE SELEÇÃO',
                'tipo_modelo' => null,
                'tipo_documento' => null,
                'tem_modelo' => false,
                'permite_assinatura' => false,
            ],
            [
                'tipo' => 'DocChecklist',
                'chave' => 'doc_checklist',
                'label' => 'DOCUMENTOS CHECK LIST ADMISSÃO',
                'tipo_modelo' => 'docchecklist',
                'tipo_documento' => null,
                'tem_modelo' => true,
                'permite_assinatura' => false,
            ],
            [
                'tipo' => 'FichaRegistrada',
                'chave' => 'ficha_registrada',
                'label' => 'FICHA REGISTRO ASSINADA',
                'tipo_modelo' => null,
                'tipo_documento' => null,
                'tem_modelo' => false,
                'permite_assinatura' => false,
            ],
            [
                'tipo' => 'AutodeclaracaoEtnicoRacial',
                'chave' => 'autodeclaracao_etnico_racial',
                'label' => 'AUTODECLARAÇÃO ÉTNICO-RACIAL',
                'tipo_modelo' => 'autodeclaracao_etnico_racial',
                'tipo_documento' => null,
                'tem_modelo' => true,
                'permite_assinatura' => false,
            ],
            [
                'tipo' => 'ContratoTrabalhoAssinado',
                'chave' => 'contrato_trabalho_assinado',
                'label' => 'CONTRATO DE TRABALHO ASSINADO',
                'tipo_modelo' => 'contratotrabalhoassinado',
                'tipo_documento' => 'contrato_trabalho',
                'tem_modelo' => true,
                'permite_assinatura' => true,
            ],
            [
                'tipo' => 'TermoConfiabilidade',
                'chave' => 'termo_confiabilidade',
                'label' => 'TERMO DE CONFIDENCIALIDADE ASSINADO',
                'tipo_modelo' => 'termoconfiabilidade',
                'tipo_documento' => 'termo_confidencialidade',
                'tem_modelo' => true,
                'permite_assinatura' => true,
            ],
            [
                'tipo' => 'ValeTransporteAssinado',
                'chave' => 'vale_transporte_assinado',
                'label' => 'OPÇÃO VALE TRANSPORTE ASSINADO',
                'tipo_modelo' => 'valetransporte',
                'tipo_documento' => 'opcao_vale_transporte',
                'tem_modelo' => true,
                'permite_assinatura' => true,
            ],
            [
                'tipo' => 'AcordoHora',
                'chave' => 'acordo_hora',
                'label' => 'ACORDO COMPENSAÇÃO DE HORAS ASSINADO',
                'tipo_modelo' => 'acordocompensacaohoras',
                'tipo_documento' => 'acordo_compensacao_horas',
                'tem_modelo' => true,
                'permite_assinatura' => true,
            ],
            [
                'tipo' => 'SalarioFamiliaAssinado',
                'chave' => 'salario_familia_assinado',
                'label' => 'TERMO SALÁRIO FAMILIA ASSINADO',
                'tipo_modelo' => 'termosalariofamilia',
                'tipo_documento' => 'termo_salario_familia',
                'tem_modelo' => true,
                'permite_assinatura' => true,
            ],
            [
                'tipo' => 'DeclaracaoDependentesImposto',
                'chave' => 'declaracao_dependentes_imposto',
                'label' => 'DECLARAÇÃO DEPENDENTES IMPOSTO DE RENDA ASSINADO',
                'tipo_modelo' => 'declaracaodependentesimposto',
                'tipo_documento' => 'declaracao_dependentes_ir',
                'tem_modelo' => true,
                'permite_assinatura' => true,
            ],
            [
                'tipo' => 'ComprovanteDevCtp',
                'chave' => 'comprovante_dev_ctp',
                'label' => 'COMPROVANTE DEVOLUÇÃO CTPS ASSINADO',
                'tipo_modelo' => null,
                'tipo_documento' => null,
                'tem_modelo' => false,
                'permite_assinatura' => false,
            ],
            [
                'tipo' => 'OrdemServicoAssinada',
                'chave' => 'ordem_servico_assinada',
                'label' => 'ORDEM DE SERVIÇO ASSINADA',
                'tipo_modelo' => null,
                'tipo_documento' => null,
                'tem_modelo' => false,
                'permite_assinatura' => false,
            ],
            [
                'tipo' => 'CertificadoTreinSeg',
                'chave' => 'certificado_trein_seg',
                'label' => 'CERTIFICADOS DE TREINAMENTOS SEGURANÇA',
                'tipo_modelo' => null,
                'tipo_documento' => null,
                'tem_modelo' => false,
                'permite_assinatura' => false,
            ],
            [
                'tipo' => 'FichaEntregaEpi',
                'chave' => 'ficha_entrega_epi',
                'label' => 'FICHA DE ENTREGA DE EPI',
                'tipo_modelo' => null,
                'tipo_documento' => null,
                'tem_modelo' => false,
                'permite_assinatura' => false,
            ],
            [
                'tipo' => 'ContraChequeMensais',
                'chave' => 'contra_cheque_mensais',
                'label' => 'CONTRACHEQUES MENSAIS',
                'tipo_modelo' => null,
                'tipo_documento' => null,
                'tem_modelo' => false,
                'permite_assinatura' => false,
            ],
            [
                'tipo' => 'CartoesPonto',
                'chave' => 'cartoes_ponto',
                'label' => 'CARTÕES DE PONTO MENSAIS',
                'tipo_modelo' => null,
                'tipo_documento' => null,
                'tem_modelo' => false,
                'permite_assinatura' => false,
            ],
            [
                'tipo' => 'AvisoFerias',
                'chave' => 'aviso_ferias',
                'label' => 'AVISOS DE FÉRIAS ANUAIS',
                'tipo_modelo' => null,
                'tipo_documento' => null,
                'tem_modelo' => false,
                'permite_assinatura' => false,
            ],
            [
                'tipo' => 'ControleAsos',
                'chave' => 'controle_asos',
                'label' => 'PASTA DE CONTROLE DE ASOS: ADMISSIONAIS, PERIODICOS,MUDANÇA DE FUNÇÃO, DEMISSIONAL',
                'tipo_modelo' => null,
                'tipo_documento' => null,
                'tem_modelo' => false,
                'permite_assinatura' => false,
            ],
            [
                'tipo' => 'BookRescisao',
                'chave' => 'book_rescisao',
                'label' => 'BOOK DE RESCISÃO – CHECK LIST DEMISSÃO:',
                'tipo_modelo' => null,
                'tipo_documento' => null,
                'tem_modelo' => false,
                'permite_assinatura' => false,
            ],
            [
                'tipo' => 'TermoRescisao',
                'chave' => 'termo_rescisao',
                'label' => 'TERMO DE RESCISAO DE CONTRATO DE TRABALHO ASSINADO',
                'tipo_modelo' => null,
                'tipo_documento' => null,
                'tem_modelo' => false,
                'permite_assinatura' => false,
            ],
            [
                'tipo' => 'GuiaSeguroDesemprego',
                'chave' => 'guia_seguro_desemprego',
                'label' => 'GUIAS DE SEGURO DESEMPREGO ASSINADAS',
                'tipo_modelo' => null,
                'tipo_documento' => null,
                'tem_modelo' => false,
                'permite_assinatura' => false,
            ],
            [
                'tipo' => 'ChaveFgts',
                'chave' => 'chave_fgts',
                'label' => 'CHAVE DE FGTS ASSINADA',
                'tipo_modelo' => null,
                'tipo_documento' => null,
                'tem_modelo' => false,
                'permite_assinatura' => false,
            ],
            [
                'tipo' => 'ComprovantePagamento',
                'chave' => 'comprovante_pagamento',
                'label' => 'COMPROVANTE DE PAGAMENTO',
                'tipo_modelo' => null,
                'tipo_documento' => null,
                'tem_modelo' => false,
                'permite_assinatura' => false,
            ],
            [
                'tipo' => 'ExameDemissional',
                'chave' => 'exame_demissional',
                'label' => 'EXAME DEMISSIONAL',
                'tipo_modelo' => null,
                'tipo_documento' => null,
                'tem_modelo' => false,
                'permite_assinatura' => false,
            ],
            [
                'tipo' => 'NadaConstaFichaEpi',
                'chave' => 'nada_consta_ficha_epi',
                'label' => 'NADA CONSTA DE BAIXA E FICHA DE ENTREGA DE EPI',
                'tipo_modelo' => null,
                'tipo_documento' => null,
                'tem_modelo' => false,
                'permite_assinatura' => false,
            ],
            [
                'tipo' => 'ComprovanteDevolucaoCtps',
                'chave' => 'comprovante_devolucao_ctps',
                'label' => 'COMPROVANTE DEVOLUÇÃO CTPS ASSINADO',
                'tipo_modelo' => null,
                'tipo_documento' => null,
                'tem_modelo' => false,
                'permite_assinatura' => false,
            ],
            [
                'tipo' => 'PppAssinado',
                'chave' => 'ppp_assinado',
                'label' => 'PPP ASSINADO',
                'tipo_modelo' => null,
                'tipo_documento' => null,
                'tem_modelo' => false,
                'permite_assinatura' => false,
            ],
            [
                'tipo' => 'ArquivamentoEletronico',
                'chave' => 'arquivamento_eletronico',
                'label' => 'CERTIFICADOS REQUISITOS BÁSICOS',
                'tipo_modelo' => null,
                'tipo_documento' => null,
                'tem_modelo' => false,
                'permite_assinatura' => false,
            ],
            [
                'tipo' => 'ArquivamentoDossie',
                'chave' => 'arquivamento_dossie',
                'label' => 'CERTIFICADOS REQUISITOS DESEJÁVEIS',
                'tipo_modelo' => null,
                'tipo_documento' => null,
                'tem_modelo' => false,
                'permite_assinatura' => false,
            ],
            [
                'tipo' => 'PlanoSaudeAssinado',
                'chave' => 'plano_saude_assinado',
                'label' => 'PLANO DE SAÚDE ASSINADO',
                'tipo_modelo' => null,
                'tipo_documento' => null,
                'tem_modelo' => false,
                'permite_assinatura' => false,
            ],
        ];

        try {
            DB::beginTransaction();

            foreach ($tipos as $index => $dados) {
                $dados['empresa_id'] = null; // catálogo global
                $dados['ordem'] = $index + 1;
                $dados['ativo'] = true;
                $dados['updated_at'] = now();

                $existente = DossieTipo::whereNull('empresa_id')->where('tipo', $dados['tipo'])->first();
                if ($existente) {
                    $existente->update($dados);
                } else {
                    $dados['created_at'] = now();
                    DossieTipo::create($dados);
                }
            }

            DossieTipo::limparCache();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
