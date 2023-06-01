<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;

class DocumentoEmpresaAdmissaoEquatorialTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $this->cadastraCategorias();

        $CAT_DOCUMENTOS_ORIGINAIS = DB::table('documentos_curriculos_cat_adm_empresa')->where('label', 'DOCUMENTOS ORIGINAIS')->where('empresa_id', 65974)->first()->id;
        $CAT_DOCUMENTOS_LEGIVEIS = DB::table('documentos_curriculos_cat_adm_empresa')->where('label', 'CÓPIA LEGÍVEL DOS DOCUMENTOS')->where('empresa_id', 65974)->first()->id;
        $CAT_SALARIO_FAMILIA = DB::table('documentos_curriculos_cat_adm_empresa')->where('label', 'PARA CADASTRO DE SALÁRIO FAMÍLIA')->where('empresa_id', 65974)->first()->id;
        $CAT_VALE_TRANSPORTE = DB::table('documentos_curriculos_cat_adm_empresa')->where('label', 'PARA VALE TRANSPORTE')->where('empresa_id', 65974)->first()->id;
        $CAT_IMPOSTO_RENDA = DB::table('documentos_curriculos_cat_adm_empresa')->where('label', 'Declaração de Dependentes para Imposto de Renda (se necessário)')->where('empresa_id', 65974)->first()->id;


        $listaDocumentos[] = [
            'empresa_id' => 65974,
            'categoria_id' => $CAT_DOCUMENTOS_ORIGINAIS,
            'label' => 'Ficha cadastral do candidato',
            'metodo' => 'FichaCadastralArquivo',
            'descricao' => 'Ficha cadastral do candidato (preenchida, datada e assinada) <a href="https://mybp-prod.s3.amazonaws.com/public/equatorialservicos_fichacadastral.pdf" target="_blank" download="ficha_cadastral.pdf" class="btn btn-sm btn-outline-primary">Baixar Modelo</a>',
            'tipo' => 'fichacadastral',
            'configuracoes' => json_encode([
                'obrigatorio' => true,
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
            'empresa_id' => 65974,
            'categoria_id' => $CAT_DOCUMENTOS_ORIGINAIS,
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
            'empresa_id' => 65974,
            'categoria_id' => $CAT_DOCUMENTOS_ORIGINAIS,
            'label' => 'Atestado de Saúde Ocupacional - ASO',
            'descricao' => '(encaminhamento fornecido pelo RH após entrega dos documentos)',
            'metodo' => 'AsoArquivo',
            'tipo' => 'aso_anexo',
            'configuracoes' => json_encode([
                'obrigatorio' => false,
                'apenas_img' => false,
                'apenas_pdf' => false,
                'apenas_pdf_img' => true,
                'multiple' => false,
                'min' => 1,
                'max' => 1,
                'sogestao' => true
            ]),
            'ativo' => true
        ];

        $listaDocumentos[] = [
            'empresa_id' => 65974,
            'categoria_id' => $CAT_DOCUMENTOS_LEGIVEIS,
            'label' => 'Folha de Rosto e Dados de Identificação da CTPS (Fisica ou digital)',
            'descricao' => 'Fisica ou digital',
            'metodo' => 'folhaRostoCtpsArquivo',
            'tipo' => 'folha_rosto_ctps',
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
            'empresa_id' => 65974,
            'categoria_id' => $CAT_DOCUMENTOS_LEGIVEIS,
            'label' => 'Carteira de Identidade Profissional',
            'descricao' => 'Conselho de Classe – Cópia autenticada em Cartório - se possuir',
            'metodo' => 'CarteiraIdentidadeProfissionalArquivo',
            'tipo' => 'carteira_identidade_profissional',
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
            'empresa_id' => 65974,
            'categoria_id' => $CAT_DOCUMENTOS_LEGIVEIS,
            'label' => 'Cópia da CNH (se possuir)',
            'descricao' => null,
            'metodo' => 'CnhArquivo',
            'tipo' => 'cnh',
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
            'empresa_id' => 65974,
            'categoria_id' => $CAT_DOCUMENTOS_LEGIVEIS,
            'label' => 'RG',
            'descricao' => null,
            'metodo' => 'RgArquivo',
            'tipo' => 'rg',
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
            'empresa_id' => 65974,
            'categoria_id' => $CAT_DOCUMENTOS_LEGIVEIS,
            'label' => 'CPF ',
            'descricao' => 'Emitido no site da Receita Federal Brasileira',
            'metodo' => 'CpfArquivo',
            'tipo' => 'cpf',
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
            'empresa_id' => 65974,
            'categoria_id' => $CAT_DOCUMENTOS_LEGIVEIS,
            'label' => 'Carteira reservista ou dispensa militar',
            'descricao' => 'Obs.: Obrigatorio para o sexo masculino',
            'metodo' => 'ReservistaArquivo',
            'tipo' => 'reservista',
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
            'empresa_id' => 65974,
            'categoria_id' => $CAT_DOCUMENTOS_LEGIVEIS,
            'label' => 'Espelho ou Resumo do PIS',
            'descricao' => 'Atendimento presencial ou online da Caixa Econômica Federal',
            'metodo' => 'PisArquivo',
            'tipo' => 'pis',
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
            'empresa_id' => 65974,
            'categoria_id' => $CAT_DOCUMENTOS_LEGIVEIS,
            'label' => 'Comprovante de Escolaridade',
            'descricao' => 'Cópia autenticada em Cartório',
            'metodo' => 'ComprovanteEscolaridadeArquivo',
            'tipo' => 'comprovante_escolaridade',
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
            'empresa_id' => 65974,
            'categoria_id' => $CAT_DOCUMENTOS_LEGIVEIS,
            'label' => 'Tipagem Sanguínea',
            'descricao' => 'Exame laboratorial - se não tiver documentação que comprove',
            'metodo' => 'TipagemSanguineaArquivo',
            'tipo' => 'tipagem_sanguinea',
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
            'empresa_id' => 65974,
            'categoria_id' => $CAT_DOCUMENTOS_LEGIVEIS,
            'label' => 'Conta de Energia Elétrica - (último mês)',
            'descricao' => null,
            'metodo' => 'ContaEnergiaArquivo',
            'tipo' => 'conta_energia',
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
            'empresa_id' => 65974,
            'categoria_id' => $CAT_DOCUMENTOS_LEGIVEIS,
            'label' => 'Comprovante de residência próprio ou em nome dos pais',
            'descricao' => 'Caso não possua, necessário autenticar a Declaração de Residência <a href="https://mybp-prod.s3.amazonaws.com/public/equatorialservicos_declaracao_residencia.pdf" target="_blank" download="declaracao_residencia.pdf" class="btn btn-sm btn-outline-primary">Baixar Modelo</a>',
            'metodo' => 'ComprovanteResidenciaArquivo',
            'tipo' => 'comprovante_residencia',
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
            'empresa_id' => 65974,
            'categoria_id' => $CAT_DOCUMENTOS_LEGIVEIS,
            'label' => 'Certidão de Nascimento, Casamento ou Escritura Pública de união estável',
            'descricao' => null,
            'metodo' => 'CertidaoNascimentoArquivo',
            'tipo' => 'certidao_nascimento',
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
            'empresa_id' => 65974,
            'categoria_id' => $CAT_DOCUMENTOS_LEGIVEIS,
            'label' => 'Conta Corrente',
            'descricao' => 'Cópia do extrato bancário - Banco do Brasil ou Bradesco',
            'metodo' => 'ContaCorrenteArquivo',
            'tipo' => 'conta_corrente',
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
            'empresa_id' => 65974,
            'categoria_id' => $CAT_DOCUMENTOS_LEGIVEIS,
            'label' => 'Carteira de vacinação atualizada',
            'descricao' => 'COVID-19, Febre Amarela e Antitetânica (validade de 10 anos).',
            'metodo' => 'CarteiraVacinaArquivo',
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
            'empresa_id' => 65974,
            'categoria_id' => $CAT_DOCUMENTOS_LEGIVEIS,
            'label' => 'Antecedentes criminais para os cargos',
            'descricao' => 'Promotor de vendas e Atendente de Call Center',
            'metodo' => 'AntecedentesCriminaisArquivo',
            'tipo' => 'antecedentes_criminais',
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
            'empresa_id' => 65974,
            'categoria_id' => $CAT_DOCUMENTOS_LEGIVEIS,
            'label' => 'Currículo atualizado',
            'descricao' => null,
            'metodo' => 'DeclaracaoEscolarFilho',
            'tipo' => 'declaracao_escolar_filho',
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
            'empresa_id' => 65974,
            'categoria_id' => $CAT_DOCUMENTOS_LEGIVEIS,
            'label' => 'Para a Cidade de São Luis-Ma',
            'descricao' => 'É necessário apresentar declaração de Sindicalizado ou Não sindicalizado, independente da opção, é necessário comparecer ao Sindicato, exceto para cargos relacionados ao Call Center. Endereço do Sindicato da Construção Civil: 1 TV República - Diamante, São Luís -MA (próximo ao Hospital Diamantes no canto da Fabril).',
            'metodo' => 'DeclaracaoNaoSindicalizadoArquivo',
            'tipo' => 'declaracao_nao_sindicalizado',
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
            'empresa_id' => 65974,
            'categoria_id' => $CAT_SALARIO_FAMILIA,
            'label' => 'Termo de Responsabilidade do Salário Família',
            'descricao' => 'Preenchido, datado e assinado <a href="https://mybp-prod.s3.amazonaws.com/public/equatorialservicos_form_salario_familia.xlsx" target="_blank" download="formulario_salario_familia.xlsx" class="btn btn-sm btn-outline-primary">Baixar Modelo</a>',
            'metodo' => 'TermoResponsabilidadeSalarioFamiliaArquivo',
            'tipo' => 'termo_responsabilidade_salario_familia',
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
            'empresa_id' => 65974,
            'categoria_id' => $CAT_SALARIO_FAMILIA,
            'label' => 'Certidão de Nascimento dos filhos com idade até 24 anos',
            'descricao' => null,
            'metodo' => 'CertidaoNascimentoFilhoArquivo',
            'tipo' => 'certidao_nascimento_filho',
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
            'empresa_id' => 65974,
            'categoria_id' => $CAT_SALARIO_FAMILIA,
            'label' => 'Caderneta de Vacinação dos filhos menores de 07 anos',
            'descricao' => null,
            'metodo' => 'CadernetaVacinaFilhoArquivo',
            'tipo' => 'caderneta_vacina_filho',
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
            'empresa_id' => 65974,
            'categoria_id' => $CAT_SALARIO_FAMILIA,
            'label' => 'Comprovante de Frequência escolar semestral (atualizado) dos filhos de 07 a 14 anos',
            'descricao' => null,
            'metodo' => 'ComprovanteFrequenciaFilhoArquivo',
            'tipo' => 'comprovante_frequencia_filho',
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
            'empresa_id' => 65974,
            'categoria_id' => $CAT_SALARIO_FAMILIA,
            'label' => 'CPF de filhos (de qualquer idade)',
            'descricao' => null,
            'metodo' => 'CpfFilhoArquivo',
            'tipo' => 'cpf_filho',
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
            'empresa_id' => 65974,
            'categoria_id' => $CAT_SALARIO_FAMILIA,
            'label' => 'Atestado de invalidez de filho de qualquer idade',
            'descricao' => null,
            'metodo' => 'AtestadoInvalidezFilhoArquivo',
            'tipo' => 'atestado_invalidez_filho',
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
            'empresa_id' => 65974,
            'categoria_id' => $CAT_SALARIO_FAMILIA,
            'label' => 'Comprovante de matrícula em universidade para filhos entre 21 a 24 anos.',
            'descricao' => null,
            'metodo' => 'ComprovanteMatriculaFilhoArquivo',
            'tipo' => 'comprovante_matricula_filho',
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
            'empresa_id' => 65974,
            'categoria_id' => $CAT_VALE_TRANSPORTE,
            'label' => 'Formulário preenchido, datado e assinado do Termo de Opção de Vale Transporte',
            'descricao' => '<a href="https://mybp-prod.s3.amazonaws.com/public/equatorialservicos_termo_valetransporte.pdf" target="_blank" download="termo_vale_transporte.pdf" class="btn btn-sm btn-outline-primary">Baixar Modelo</a>',
            'metodo' => 'TermoOpcaoValeTransporteArquivo',
            'tipo' => 'termo_opcao_vale_transporte',
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
            'empresa_id' => 65974,
            'categoria_id' => $CAT_VALE_TRANSPORTE,
            'label' => 'Cópia do cartão de Vale Transporte Digital',
            'descricao' => null,
            'metodo' => 'CartaoValeTransporteDigitalArquivo',
            'tipo' => 'cartao_vale_transporte_digital',
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
            'empresa_id' => 65974,
            'categoria_id' => $CAT_VALE_TRANSPORTE,
            'label' => 'Conta de Energia Elétrica (Último mês - 1 cópia)',
            'descricao' => null,
            'metodo' => 'ContaEnergiaEletricaArquivo',
            'tipo' => 'conta_energia_eletrica',
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
            'empresa_id' => 65974,
            'categoria_id' => $CAT_IMPOSTO_RENDA,
            'label' => 'RG e CPF dos dependentes',
            'descricao' => null,
            'metodo' => 'RgCpfDependenteArquivo',
            'tipo' => 'rg_cpf_dependente',
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
            'empresa_id' => 65974,
            'categoria_id' => $CAT_IMPOSTO_RENDA,
            'label' => 'Certidão de Nascimento dos filhos',
            'descricao' => 'Caso não possuam RG e CPF',
            'metodo' => 'RgCpfDependenteArquivo',
            'tipo' => 'rg_cpf_dependente',
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
            'empresa_id' => 65974,
            'categoria_id' => $CAT_IMPOSTO_RENDA,
            'label' => 'CPF de filhos (de qualquer idade)',
            'descricao' => null,
            'metodo' => 'CpfFilhoQlqIdadeArquivo',
            'tipo' => 'cpf_filho_qlq_idade',
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
            'empresa_id' => 65974,
            'categoria_id' => $CAT_IMPOSTO_RENDA,
            'label' => 'Certidão de Casamento ou Escritura Pública de união estável',
            'descricao' => null,
            'metodo' => 'CertidaoCasamentoArquivo',
            'tipo' => 'certidao_casamento',
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

        try {
            DB::beginTransaction();

            $clientes = DB::table('clientes')->where('id', 65974)->select('id')->get();
            foreach ($clientes as $cliente) {
                $cont = 1;
                foreach ($listaDocumentos as $doc) {
                    $doc['empresa_id'] = $cliente->id;
                    $doc['ordem'] = $cont++;
                    if (DB::table('documentos_curriculos_adm_empresa')->where('tipo', $doc['tipo'])->where('empresa_id', $doc['empresa_id'])->count() > 0)
                        continue;
                    DB::table('documentos_curriculos_adm_empresa')->insert($doc);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage(), $e->getCode(), $e->getLine(), $e->getTraceAsString());
        }
    }

    public function cadastraCategorias()
    {
        try {
            DB::beginTransaction();
            $listaCategorias[] = [
                'empresa_id' => 65974,
                'label' => 'DOCUMENTOS ORIGINAIS'
            ];

            $listaCategorias[] = [
                'empresa_id' => 65974,
                'label' => 'CÓPIA LEGÍVEL DOS DOCUMENTOS'
            ];

            $listaCategorias[] = [
                'empresa_id' => 65974,
                'label' => 'PARA CADASTRO DE SALÁRIO FAMÍLIA'
            ];

            $listaCategorias[] = [
                'empresa_id' => 65974,
                'label' => 'PARA VALE TRANSPORTE'
            ];

            $listaCategorias[] = [
                'empresa_id' => 65974,
                'label' => 'Declaração de Dependentes para Imposto de Renda (se necessário)'
            ];

            foreach ($listaCategorias as $categoria) {
                if (DB::table('documentos_curriculos_cat_adm_empresa')->where('label', $categoria['label'])->where('empresa_id', $categoria['empresa_id'])->count() > 0)
                    continue;
                DB::table('documentos_curriculos_cat_adm_empresa')->insert($categoria);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage(), $e->getCode(), $e->getLine(), $e->getTraceAsString());
        }
    }

}
