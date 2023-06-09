@extends('layouts.pdf_filial')
@section('title','Relatorio de ponto')
@section('conteudo')
    <div style="margin-left: 9px">
        @include('layouts.cabecalioFilialEmpresaJob')
    </div>
    <div style="margin-left: 9px">
        <p class="f12"
           style="text-align: center; margin-bottom: 1cm; margin-top: 0.5cm; text-transform: uppercase">
            <b>DOCUMENTOS PARA ADMISSÃO</b>
        </p>
        <table border="1" cellpadding="0" cellspacing="0" class="dados2" style="font-size: 0.85em; width: 97%">
            <thead>
            <tr class="f10">
                <th class="text-center"></th>
                <th class="text-center">Status</th>
            </tr>
            </thead>
            <tbody class="f10">
            <tr>
                <td>Carteira Profissional (Somente no dia da ADMISSÃO)</td>
                <td></td>
            </tr>
            <tr>
                <td>03 fotos 3x4 recentes</td>
                <td></td>
            </tr>
            <tr>
                <td>Cópia da Carteira de Identidade</td>
                <td></td>
            </tr>
            <tr>
                <td>Cópia do CPF</td>
                <td></td>
            </tr>
            <tr>
                <td>Cópia do Título de Eleitor</td>
                <td></td>
            </tr>
            <tr>
                <td>Cópia da Carteira Profissional (frente, verso)</td>
                <td></td>
            </tr>
            <tr>
                <td>Cópia do Comprovante de Residência (contas de água, luz ou telefone) com CEP</td>
                <td></td>
            </tr>
            <tr>
                <td>Cópia do Cartão de PIS (Cartão do Cidadão)</td>
                <td></td>
            </tr>
            <tr>
                <td>Cópia da Carteira Nacional de Habilitação (CNH)</td>
                <td></td>
            </tr>
            <tr>
                <td>Cópia da Certidão de Nascimento, Casamento ou Declaração de União Estável Pública</td>
                <td></td>
            </tr>
            <tr>
                <td>Cópia do Certificado de Reservista/Alistamento no Serviço Militar</td>
                <td></td>
            </tr>
            <tr>
                <td>Cópia da Carteira de Vacina (Colaborador) (Febre/Hepatite B)</td>
                <td></td>
            </tr>
            <tr>
                <td>Cópia do Certificado de Conclusão/Declaração ou Histórico Escolar</td>
                <td></td>
            </tr>
            <tr>
                <td>Cópias dos Certificados dos Cursos de Capacitação</td>
                <td></td>
            </tr>
            </tbody>
        </table>
        <div class="f10" style="line-height: 26pt;text-align: center">
            <br>
            Favor aprensentar todos os documentos no dia ____/____ /_______.<br><br>
        </div>
        <p class="f10">
            Recebido por: __________________________________________________<br><br>
            Em: ________________________________________________________
        </p><br><br>
        <p class="f10" style="line-height: 22pt;text-align: justify; width: 96.7%">
            * Em atendimento a Lei nº 13.709/2018, Lei Geral de Proteção de Dados, viemos através deste solicitar o
            consentimento do uso dos dados acima para fins de processsamento da admissão
            na {{ $dados['dados_empresa']['razao_social']  }} por prazo
            indeterminado assim como registro nos órgãos competetes e permitir a gestão de pessoas por parte da empresa.
            Comunicamos ainda que as cópias das documentações solicitadas serão de uso exclusivo da contabilidade e
            responsável pela recursos humanos da empresa. Após o processo de desligamento, a documentação será
            armazenada
            como funcionário inativo, pelo período de legal de 30 anos de forma digital e física. Pode ocorrer que
            precisemos compartilhar seus dados com terceiros para cumprir exigencias legais, regulatórias os fiscais
            envolvendo a divulgação dos seus dados pessoais a terceiros, a um tribunal, reguladores ou agencias
            governamentais.
        </p>
        <div style="position:fixed; bottom: 35px">
            @include('layouts.rodapePdfFilialJob')
        </div>
    </div>
@stop

@push('style')
    <style type="text/css">

        .f14 {
            font-size: 14pt !important
        }

        .f11 {
            font-size: 11pt !important
        }

        .f12 {
            font-size: 12pt !important;
        }

        .f10 {
            font-size: 10pt !important;
        }

        .obs {
            font-size: 8.4pt;
            color: #444444;
            margin-bottom: 10px;
        }

    </style>
@endpush
