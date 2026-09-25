@extends('layouts.pdf_filial')
@section('title','Declaração de Ciência e Concordância – Plano de Saúde')
@section('conteudo')
    @php
        $admissao = $dados['dados_colaborador']->Admissao ?? null;
        $dataAdmissaoRaw = $admissao?->getAttributes()['data_admissao'] ?? null;
        $dataAssinatura = $dataAdmissaoRaw
            ? (new \MasterTag\DataHora($dataAdmissaoRaw))->dataCompletaExt()
            : (new \MasterTag\DataHora())->dataCompletaExt();
        $nomeColaborador = $dados['dados_colaborador']->Curriculo?->nome ?? 'NÃO INFORMADO';
    @endphp
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
           style="text-align: center; margin-bottom: 0.9cm; margin-top: 0.5cm; text-transform: uppercase">
            <br>
            <strong>DECLARAÇÃO DE CIÊNCIA E CONCORDÂNCIA</strong><br>
            <strong>PLANO DE SAÚDE - HAPVIDA</strong>
        </p>

        <p class="f11" style="line-height: 18pt; text-align: justify">
            Declaro, para os devidos fins, que estou ciente das condições referentes ao Plano de Saúde,
            cujo valor mensal é de <strong>R$ 301,63</strong> (trezentos e um reais e sessenta e três centavos).
        </p>

        <p class="f11" style="line-height: 18pt; text-align: justify">
            Estou ciente de que o valor do plano é dividido igualmente entre a empresa e o colaborador,
            sendo <strong>50%</strong> do valor custeado pela empresa e <strong>50%</strong> pelo colaborador.
        </p>

        <p class="f11" style="line-height: 18pt; text-align: justify">
            Estou de acordo com o desconto da minha participação no valor do plano em folha de pagamento,
            conforme as condições estabelecidas pela empresa.
        </p>

        <p class="f11" style="line-height: 18pt; margin-top: 18pt; margin-bottom: 8pt;">
            <strong>VALORES DO PLANO</strong>
        </p>

        <table class="f11" style="width: 100%; border-collapse: collapse; line-height: 20pt; margin-bottom: 14pt;">
            <tr>
                <td style="border: 1px solid #333; padding: 6px 8px; width: 55%;"><strong>Valor total do plano</strong></td>
                <td style="border: 1px solid #333; padding: 6px 8px;">R$ 301,63</td>
            </tr>
            <tr>
                <td style="border: 1px solid #333; padding: 6px 8px;"><strong>Parte custeada pela empresa (50%)</strong></td>
                <td style="border: 1px solid #333; padding: 6px 8px;">R$ 150,81</td>
            </tr>
            <tr>
                <td style="border: 1px solid #333; padding: 6px 8px;">
                    <strong>Parte custeada pelo colaborador (50%)</strong>
                </td>
                <td style="border: 1px solid #333; padding: 6px 8px;">
                    R$ 150,82 – DESCONTADO EM FOLHA DE PAGAMENTO.
                </td>
            </tr>
        </table>

        <p class="f11" style="line-height: 18pt; text-align: justify">
            Declaro estar ciente e de acordo com as condições acima descritas.
        </p>

        <br><br><br>
        <div class="f11" style="text-align: center; line-height: 16pt;">
            <hr style="width: 8cm; margin: 0 auto; border: none; border-top: 1px solid #333">
            <div style="margin-top: 4px;">
                {{ $nomeColaborador }}<br>
                <span style="font-size: 9pt;">COLABORADOR(A)</span>
            </div>
        </div>

        <br><br>
        <div class="f11" style="line-height: 18pt;">
            São Luís, {{ $dataAssinatura }}.
        </div>
    </div>
@stop

@push('style')
    <style type="text/css">
        .f12 {
            font-size: 12pt !important;
        }

        .f11 {
            font-size: 11pt !important;
        }
    </style>
@endpush
