@extends('layouts.pdf_filial')
@section('title','COMUNICADO – PLANO DE SAÚDE CORPORATIVO')
@section('conteudo')
    <style>
        @page {
            margin: 10mm 2mm 8mm 2mm;
        }
    </style>
    <div style="margin-left: 9px">
        @include('layouts.cabecalioFilialEmpresaJob')
    </div>
    <div style="position: fixed; left: 20px; bottom: 0; text-align: left; width: 90%; padding-bottom: 2px;">
        @include('layouts.rodapePdfFilialJob')
    </div>
    <div style="margin-left: 2.5%; width: 93%; padding-bottom: 28px;">
        <p class="f12"
           style="text-align: center; margin-bottom: 1cm; margin-top: 0.5cm; text-transform: uppercase">
            <br><br>
            <strong>COMUNICADO – PLANO DE SAÚDE CORPORATIVO</strong><br>
        </p>

        <p class="f11" style="line-height: 18pt; text-align: justify">
            A empresa <strong>{{ $dados['dados_empresa']['razao_social'] ?? '' }}</strong>,
            inscrita no CNPJ sob o nº <strong>{{ $dados['dados_empresa']['cnpj'] ?? '' }}</strong>,
            informa que disponibiliza aos seus colaboradores a possibilidade de inclusão de dependentes no plano de saúde corporativo,
            conforme as regras vigentes da operadora e as políticas internas da empresa.
        </p>

        <p class="f11" style="line-height: 18pt; text-align: justify">
            Fica ciente o colaborador de que, em caso de afastamento por qualquer motivo (incluindo doença, acidente ou qualquer outro tipo de licença, remunerada ou não),
            os dependentes incluídos no plano de saúde serão automaticamente excluídos, permanecendo apenas a cobertura do titular.
            A reinclusão dos dependentes poderá ser solicitada após o retorno do colaborador às suas atividades laborais.
        </p>

        <p class="f11" style="line-height: 18pt; text-align: justify">
            Comunicamos ainda que, a partir do dia <strong>1º de setembro de 2025</strong>, será implementada a coparticipação no plano de saúde para internações psiquiátricas,
            conforme previsto em contrato e em conformidade com a Resolução Normativa nº 465/2021 da ANS (Agência Nacional de Saúde Suplementar) – Subseção III, Art. 19.
        </p>

        <p class="f11" style="line-height: 18pt; text-align: justify">
            A coparticipação será devida somente quando o tempo de internação ou o número de consultas ultrapassar 30 (trinta) dias, consecutivos ou não,
            dentro do ano contratual de cada beneficiário (contado a partir da data de adesão ao plano).
        </p>

        <p class="f11" style="line-height: 18pt; text-align: justify">
            O valor correspondente à coparticipação será cobrado através de boleto bancário gerado pela operadora do plano de saúde,
            sendo de responsabilidade do colaborador o seu pagamento.
        </p>

        <p class="f11" style="line-height: 18pt;">
            Exemplos de procedimentos psiquiátricos que poderão gerar coparticipação:
        </p>
        <ul class="f11" style="line-height: 16pt; margin-top: 0;">
            <li>Internações hospitalares para tratamento psiquiátrico;</li>
            <li>Consultas médicas com psiquiatra;</li>
            <li>Sessões de psicoterapia (com psicólogos ou terapeutas credenciados);</li>
            <li>Atendimentos em pronto-socorro psiquiátrico.</li>
        </ul>

        <p class="f11" style="line-height: 18pt; text-align: justify">
            Ressaltamos que esta medida segue rigorosamente as normas estabelecidas pela operadora do plano de saúde,
            visando garantir a continuidade e a sustentabilidade deste benefício a todos os colaboradores.
        </p>

        <p class="f11" style="line-height: 18pt; text-align: justify">
            Para esclarecimentos adicionais sobre valores, regras de coparticipação e demais informações,
            orientamos que os colaboradores entrem em contato com o setor de Recursos Humanos.
        </p>

        <br>
        <div class="f11" style="line-height: 26pt">
            São Luís - MA, {{ (new \MasterTag\DataHora())->dataCompletaExt() }}.
        </div>

        <br><br><br>
        <table style="width: 100%; border: none;">
            <tr>
                <td style="width: 50%; text-align: center; border: none; vertical-align: top;">
                    <hr style="width: 7cm; margin: 0 auto; border: none; border-top: 1px solid #333">
                    <div class="f11" style="margin-top: 4px;">
                        {{ $dados['dados_empresa']['razao_social'] ?? '' }}<br>
                        <span style="font-size: 9pt;">EMPRESA</span>
                    </div>
                </td>
                <td style="width: 50%; text-align: center; border: none; vertical-align: top;">
                    <hr style="width: 7cm; margin: 0 auto; border: none; border-top: 1px solid #333">
                    <div class="f11" style="margin-top: 4px;">
                        {{ $dados['dados_colaborador']->Curriculo->nome ?? '' }}<br>
                        <span style="font-size: 9pt;">COLABORADOR(A)</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>
@stop

@push('style')
    <style type="text/css">
        .obs {
            font-size: 8.4pt;
            color: #444444;
            margin-bottom: 10px;
        }
    </style>
@endpush
