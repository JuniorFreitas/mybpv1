<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;

class DocumentoEmpresaAdmissaoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->cadastraCategorias();


        $PERT_VALE_TRANSPORTE = [

        ];

        $listaDocumentos[] = [
            'label' => 'FOTO 3X4',
            'metodo' => 'FotoTres',
            'descricao' => 'Obs.: Somente imagens no formato JPG, JPEG, PNG',
            'tipo' => 'foto3x4',
            'configuracoes' => json_encode([
                'obrigatorio' => true,
                'apenas_img' => true,
                'apenas_pdf' => false,
                'apenas_pdf_img' => false,
                'multiple' => false,
                'min' => 1,
                'max' => 1,
                'sogestao' => false
            ]),
            'ativo' => true
        ];

        $listaDocumentos[] = [
            'label' => 'RG/CPF',
            'descricao' => null,
            'metodo' => 'AnexosCpfRg',
            'tipo' => 'anexoscpfrg',
            'configuracoes' => json_encode([
                'obrigatorio' => false,
                'apenas_img' => false,
                'apenas_pdf' => false,
                'apenas_pdf_img' => true,
                'multiple' => false,
                'min' => 1,
                'max' => 1,
                'sogestao' => false
            ]),
            'ativo' => true
        ];

        $listaDocumentos[] = [
            'label' => 'COMPROVANTE DE ENDEREÇO',
            'descricao' => null,
            'metodo' => 'ComprovanteEnd',
            'tipo' => 'comprovante_end',
            'configuracoes' => json_encode([
                'obrigatorio' => false,
                'apenas_img' => false,
                'apenas_pdf' => false,
                'apenas_pdf_img' => true,
                'multiple' => false,
                'min' => 1,
                'max' => 1,
                'sogestao' => false
            ]),
            'ativo' => true
        ];

        $listaDocumentos[] = [
            'label' => 'CTPS DIGITAL (FRENTE)',
            'descricao' => null,
            'metodo' => 'CtpsFrente',
            'tipo' => 'ctps_frente',
            'configuracoes' => json_encode([
                'obrigatorio' => false,
                'apenas_img' => false,
                'apenas_pdf' => false,
                'apenas_pdf_img' => true,
                'multiple' => false,
                'min' => 1,
                'max' => 1,
                'sogestao' => false
            ]),
            'ativo' => true
        ];

        $listaDocumentos[] = [
            'label' => 'CTPS DIGITAL (VERSO)',
            'descricao' => null,
            'metodo' => 'CtpsVerso',
            'tipo' => 'ctps_verso',
            'configuracoes' => json_encode([
                'obrigatorio' => false,
                'apenas_img' => false,
                'apenas_pdf' => false,
                'apenas_pdf_img' => true,
                'multiple' => false,
                'min' => 1,
                'max' => 1,
                'sogestao' => false
            ]),
            'ativo' => true
        ];

        $listaDocumentos[] = [
            'label' => 'ANTECEDENTE CRIMINAL',
            'descricao' => 'SITE PARA EMISSÃO DO ANTECEDENTE: <a href="https://servicos.dpf.gov.br/antecedentes-criminais/certidao" target="_blank">CLIQUE AQUI</a>',
            'metodo' => 'Antecedentes',
            'tipo' => 'antecedentes',
            'configuracoes' => json_encode([
                'obrigatorio' => false,
                'apenas_img' => false,
                'apenas_pdf' => false,
                'apenas_pdf_img' => true,
                'multiple' => false,
                'min' => 1,
                'max' => 1,
                'sogestao' => false
            ]),
            'ativo' => true
        ];

        $listaDocumentos[] = [
            'label' => 'TITULO ELEITOR',
            'descricao' => null,
            'metodo' => 'TituloEleitor',
            'tipo' => 'titulo_eleitor',
            'configuracoes' => json_encode([
                'obrigatorio' => false,
                'apenas_img' => false,
                'apenas_pdf' => false,
                'apenas_pdf_img' => true,
                'multiple' => false,
                'min' => 1,
                'max' => 1,
                'sogestao' => false
            ]),
            'ativo' => true
        ];

        $listaDocumentos[] = [
            'label' => 'CERTIFICADO RESERVISTA (APENAS HOMENS)',
            'descricao' => null,
            'metodo' => 'CertificadoReservista',
            'tipo' => 'certificado_reservista',
            'configuracoes' => json_encode([
                'obrigatorio' => false,
                'apenas_img' => false,
                'apenas_pdf' => false,
                'apenas_pdf_img' => true,
                'multiple' => false,
                'min' => 1,
                'max' => 1,
                'sogestao' => false
            ]),
            'ativo' => true
        ];

        $listaDocumentos[] = [
            'label' => 'CARTÃO DO PIS OU RESCISÃO DE CONTRATO',
            'descricao' => null,
            'metodo' => 'PisRescisao',
            'tipo' => 'pis_rescisao',
            'configuracoes' => json_encode([
                'obrigatorio' => false,
                'apenas_img' => false,
                'apenas_pdf' => false,
                'apenas_pdf_img' => true,
                'multiple' => false,
                'min' => 1,
                'max' => 1,
                'sogestao' => false
            ]),
            'ativo' => true
        ];

        $listaDocumentos[] = [
            'label' => 'CERTIFICADO DE ESCOLARIDADE',
            'descricao' => null,
            'metodo' => 'CertificadoEscolaridade',
            'tipo' => 'certificado_escolaridade',
            'configuracoes' => json_encode([
                'obrigatorio' => false,
                'apenas_img' => false,
                'apenas_pdf' => false,
                'apenas_pdf_img' => true,
                'multiple' => false,
                'min' => 1,
                'max' => 1,
                'sogestao' => false
            ]),
            'ativo' => true
        ];

        $listaDocumentos[] = [
            'label' => 'CONTA BANCO',
            'descricao' => null,
            'metodo' => 'ContaBanco',
            'tipo' => 'conta_banco',
            'configuracoes' => json_encode([
                'obrigatorio' => false,
                'apenas_img' => false,
                'apenas_pdf' => false,
                'apenas_pdf_img' => true,
                'multiple' => false,
                'min' => 1,
                'max' => 1,
                'sogestao' => false
            ]),
            'ativo' => true
        ];

        $listaDocumentos[] = [
            'label' => 'CARTA DE SINDICALIZAÇÃO EMITIDA PELO SINDICATO',
            'descricao' => null,
            'metodo' => 'CartaSindicato',
            'tipo' => 'carta_sindicato',
            'configuracoes' => json_encode([
                'obrigatorio' => false,
                'apenas_img' => false,
                'apenas_pdf' => false,
                'apenas_pdf_img' => true,
                'multiple' => false,
                'min' => 1,
                'max' => 1,
                'sogestao' => false
            ]),
            'ativo' => true
        ];

        $listaDocumentos[] = [
            'label' => 'CÓPIA DA CARTEIRA DE VACINA',
            'descricao' => 'NÃO OBRIGATÓRIO',
            'metodo' => 'CarteiraVacina',
            'tipo' => 'carteira_vacina',
            'configuracoes' => json_encode([
                'obrigatorio' => false,
                'apenas_img' => false,
                'apenas_pdf' => false,
                'apenas_pdf_img' => true,
                'multiple' => false,
                'min' => 1,
                'max' => 1,
                'sogestao' => false
            ]),
            'ativo' => true
        ];

        $listaDocumentos[] = [
            'label' => 'DOCUMENTAÇÃO FILHOS (PARA SALÁRIO FAMÍLIA)',
            'descricao' => null,
            'metodo' => 'RgcpfFilho',
            'tipo' => 'rgcpf_filho',
            'configuracoes' => json_encode([
                'obrigatorio' => false,
                'apenas_img' => false,
                'apenas_pdf' => false,
                'apenas_pdf_img' => true,
                'multiple' => false,
                'min' => 1,
                'max' => 100,
                'sogestao' => false
            ]),
            'ativo' => true
        ];

        $listaDocumentos[] = [
            'label' => 'CARTÃO VACINA (ATÉ 6 ANOS)',
            'descricao' => null,
            'metodo' => 'CartaoVacinaFilho',
            'tipo' => 'cartao_vacina_filho',
            'configuracoes' => json_encode([
                'obrigatorio' => false,
                'apenas_img' => false,
                'apenas_pdf' => false,
                'apenas_pdf_img' => true,
                'multiple' => false,
                'min' => 1,
                'max' => 100,
                'sogestao' => false
            ]),
            'ativo' => true
        ];

        $listaDocumentos[] = [
            'label' => 'DECLARAÇÃO ESCOLAR (DE 7 ANOS ATÉ 14 ANOS)',
            'descricao' => 'DECLARAÇÃO ESCOLAR DO ANO EM CURSO (ORIGINAL)',
            'metodo' => 'DeclaracaoEscolarFilho',
            'tipo' => 'declaracao_escolar_filho',
            'configuracoes' => json_encode([
                'obrigatorio' => false,
                'apenas_img' => false,
                'apenas_pdf' => false,
                'apenas_pdf_img' => true,
                'multiple' => false,
                'min' => 1,
                'max' => 100,
                'sogestao' => false
            ]),
            'ativo' => true
        ];

        try {
            DB::beginTransaction();

            $PERT_CAT_DOC_PESSOAIS = [
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
            ];

            $PERT_CAT_DOC_FILHOS = [
                'RgcpfFilho',
                'CertidaoNascimento',
                'CartaoVacinaFilho',
                'DeclaracaoEscolarFilho',
            ];

            $clientes = DB::table('clientes')->whereNotIn('id', [65974])->select('id')->get();
            foreach ($clientes as $cliente) {
                $cont = 1;
                foreach ($listaDocumentos as $doc) {
                    $doc['empresa_id'] = $cliente->id;
                    $doc['ordem'] = $cont++;
                    if (in_array($doc['metodo'], $PERT_CAT_DOC_PESSOAIS)) {
                        $doc['categoria_id'] = DB::table('documentos_curriculos_cat_adm_empresa')->where('label', 'DOCUMENTOS PESSOAIS')->where('empresa_id', $cliente->id)->first()->id;
                    }
                    if (in_array($doc['metodo'], $PERT_CAT_DOC_FILHOS)) {
                        $doc['categoria_id'] = DB::table('documentos_curriculos_cat_adm_empresa')->where('label', 'DOCUMENTAÇÃO FILHOS (PARA SALÁRIO FAMÍLIA)')->where('empresa_id', $cliente->id)->first()->id;
                    }
                    if (in_array($doc['metodo'], $PERT_VALE_TRANSPORTE)) {
                        $doc['categoria_id'] = DB::table('documentos_curriculos_cat_adm_empresa')->where('label', 'VALE TRANSPORTE')->where('empresa_id', $cliente->id)->first()->id;
                    }

                    if (DB::table('documentos_curriculos_adm_empresa')->where('tipo', $doc['tipo'])
                            ->where('empresa_id', $doc['empresa_id'])->count() > 0)
                        continue;
                    DB::table('documentos_curriculos_adm_empresa')->insert($doc);

                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage(), $e->getCode(), $e->getLine());
        }
    }

    public function cadastraCategorias()
    {
        try {
            DB::beginTransaction();

            $clientes = DB::table('clientes')->whereNotIn('id', [65974])->select('id')->get();

            $listaCategorias[] = ['label' => 'DOCUMENTOS PESSOAIS'];
            $listaCategorias[] = ['label' => 'DOCUMENTAÇÃO FILHOS (PARA SALÁRIO FAMÍLIA)'];
            $listaCategorias[] = ['label' => 'VALE TRANSPORTE'];

            foreach ($clientes as $cliente) {
                foreach ($listaCategorias as $categoria) {
                    $categoria['empresa_id'] = $cliente->id;
                    if (DB::table('documentos_curriculos_cat_adm_empresa')->where('label', $categoria['label'])->where('empresa_id', $categoria['empresa_id'])->count() > 0)
                        continue;
                    DB::table('documentos_curriculos_cat_adm_empresa')->insert($categoria);
                }
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage(), $e->getCode(), $e->getLine(), $e->getTraceAsString());
        }
    }
}
