@extends('layouts.pdf_filial')
@section('title','Check List de Documentos')
@section('conteudo')
    <style>
        @page {
            margin: 12mm 10mm 12mm 10mm;
        }
    </style>
    <div style="width: 100%; border: 1.5px solid #000; padding: 0;">
        <table cellpadding="0" cellspacing="0" style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 28%; padding: 10px 8px; vertical-align: middle; border-bottom: 1.5px solid #000;">
                    @if(!empty($dados['dados_empresa']['logo']))
                        <img
                            src="{{ $dados['dados_empresa']['logo'] }}"
                            alt="Grupo Coimbra Alves"
                            style="height: 42px; display: block; margin: 0 auto 4px auto;"
                        >
                    @endif
                    <div style="text-align: center; font-size: 8.5pt; font-weight: bold; color: #1a4a8a; line-height: 11pt;">
                        GRUPO<br>COIMBRA ALVES
                    </div>
                </td>
                <td style="padding: 10px 8px; vertical-align: middle; border-bottom: 1.5px solid #000; border-left: 1.5px solid #000;">
                    <div style="text-align: center; font-size: 14pt; font-weight: bold; text-transform: uppercase;">
                        CHECK LIST DE DOCUMENTOS
                    </div>
                </td>
            </tr>
        </table>

        <table cellpadding="0" cellspacing="0" style="width: 100%; border-collapse: collapse;">
            <tr>
                <td colspan="2" style="text-align: center; font-weight: bold; font-size: 11pt; padding: 7px 4px; border-bottom: 1.5px solid #000;">
                    Documentos Pessoais
                </td>
            </tr>

            @php
                $documentosPessoais = [
                    '01 - RG (Atualizado)',
                    '01 - CNH Série "B", "D" e "E" atualizada (Apenas para a função de motorista)',
                    '01 - Comprovante de residência com CEP da rua ( legível digital)',
                    '01 - Comprovante da Situação Cadastral CPF (Pode ser obtido no www.receita.fazenda.gov.br)',
                    '01 - Certidão de Nascimento / Casamento',
                    '01 - Diploma ou Certificado de escolaridade ou Declaração',
                    '01 - Registro de Técnico (Carteira profissional)',
                    '01 - Carteira de Trabalho (Digital)',
                    '01 - Cartão de Vale transporte (Caso opte)',
                    '01 - Cópia da Certidão de Sindicalizado (Se sindicalizado)',
                ];
            @endphp

            @foreach($documentosPessoais as $item)
                <tr>
                    <td style="width: 28px; border-bottom: 1px solid #000; border-right: 1px solid #000; height: 22px;"></td>
                    <td style="padding: 4px 6px; border-bottom: 1px solid #000; font-size: 9.5pt;">{{ $item }}</td>
                </tr>
            @endforeach

            <tr>
                <td colspan="2" style="text-align: center; color: #1a4a8a; font-size: 9.5pt; padding: 8px 4px; border-bottom: 1.5px solid #000;">
                    Envio de documentos somente em formato PDF.
                </td>
            </tr>

            <tr>
                <td colspan="2" style="text-align: center; font-weight: bold; font-size: 11pt; padding: 7px 4px; border-bottom: 1.5px solid #000; text-transform: uppercase;">
                    Documentos de Dependentes
                </td>
            </tr>

            @php
                $documentosDependentes = [
                    '01 - Certidão de Nascimento de filhos menores de 14 anos. (Obrigatório)',
                    '01 - RG e CPF filhos menores de 14 anos (Obrigatório)',
                    '01 - Carteira de Vacinação regularizada até 06 anos. (Obrigatório)',
                    '01 - Declaração Escolar de 07 a 14 anos. (Obrigatório)',
                    '01 - CPF conjuge ( caso seja dependentes para IRRF )',
                ];
            @endphp

            @foreach($documentosDependentes as $item)
                <tr>
                    <td style="width: 28px; border-bottom: 1px solid #000; border-right: 1px solid #000; height: 22px;"></td>
                    <td style="padding: 4px 6px; border-bottom: 1px solid #000; font-size: 9.5pt;">{{ $item }}</td>
                </tr>
            @endforeach

            <tr>
                <td colspan="2" style="text-align: center; color: #1a4a8a; font-size: 9.5pt; padding: 8px 4px; border-bottom: 1.5px solid #000;">
                    Envio de documentos somente em formato PDF.
                </td>
            </tr>
        </table>

        <div style="text-align: right; font-size: 8pt; padding: 8px 10px 10px 10px;">
            FR.RH.03.00
        </div>
    </div>
@stop
