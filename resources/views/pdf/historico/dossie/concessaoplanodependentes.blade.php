@extends('layouts.pdf_filial')
@section('title','Autorização para inclusão de dependente no Plano de Saúde – HAPVIDA')
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
           style="text-align: center; margin-bottom: 0.8cm; margin-top: 0.5cm; text-transform: uppercase">
            <br>
            <strong>Autorização para inclusão de dependente<br>no Plano de Saúde – HAPVIDA</strong>
        </p>

        <p class="f11" style="line-height: 18pt; text-align: justify">
            Eu, <strong>{{ $dados['dados_colaborador']->Curriculo->nome }}</strong>,
            inscrito(a) no CPF sob o nº <strong>{{ $dados['dados_colaborador']->Curriculo->cpf ?? '' }}</strong>,
            venho por meio desta declarar que é de meu interesse incluir os meus dependentes abaixo no
            <strong>PLANO DE SAÚDE – HAPVIDA</strong>, oferecido pela empresa
            <strong>{{ $dados['dados_empresa']['razao_social'] ?? '' }}</strong>,
            onde irei pagar o valor integral por dependente.
        </p>

        <p class="f11" style="line-height: 18pt; text-align: justify">
            Fico ciente de que, em caso de afastamento <strong>NÃO REMUNERADO</strong> por qualquer motivo
            (incluindo doença, acidente ou qualquer outro tipo de licença), os dependentes incluídos no plano de saúde
            serão automaticamente excluídos, permanecendo apenas a cobertura do titular.
            A reinclusão dos dependentes poderá ser solicitada após o retorno do colaborador às suas atividades laborais.
        </p>

        <p class="f11" style="line-height: 18pt; text-align: justify">
            O custo do plano de saúde do dependente é exclusivo do funcionário.
        </p>

        <p class="f11" style="line-height: 18pt;">
            <strong>Valor por dependente:</strong> R$ 301,63
            <br>
            Lembrando que, no primeiro mês, é pago uma taxa de adesão no valor de <strong>R$ 22,80</strong> por dependente.
        </p>

        <p class="f11" style="line-height: 18pt;">
            <strong>Nome do funcionário:</strong> {{ $dados['dados_colaborador']->Curriculo->nome }}
        </p>

        <p class="f11" style="line-height: 18pt; margin-bottom: 0.3cm;">
            <strong>Dados do dependente para inclusão:</strong>
        </p>

        <table class="f11" style="width: 100%; border-collapse: collapse; line-height: 22pt;">
            <tr>
                <td style="border: 1px solid #333; padding: 4px 8px; width: 30%;"><strong>Nome completo</strong></td>
                <td style="border: 1px solid #333; padding: 4px 8px;">&nbsp;</td>
            </tr>
            <tr>
                <td style="border: 1px solid #333; padding: 4px 8px;"><strong>Parentesco</strong></td>
                <td style="border: 1px solid #333; padding: 4px 8px;">&nbsp;</td>
            </tr>
            <tr>
                <td style="border: 1px solid #333; padding: 4px 8px;"><strong>Data de nascimento</strong></td>
                <td style="border: 1px solid #333; padding: 4px 8px;">&nbsp;&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
            </tr>
            <tr>
                <td style="border: 1px solid #333; padding: 4px 8px;"><strong>CPF</strong></td>
                <td style="border: 1px solid #333; padding: 4px 8px;">&nbsp;</td>
            </tr>
            <tr>
                <td style="border: 1px solid #333; padding: 4px 8px;"><strong>Estado civil</strong></td>
                <td style="border: 1px solid #333; padding: 4px 8px;">&nbsp;</td>
            </tr>
            <tr>
                <td style="border: 1px solid #333; padding: 4px 8px;"><strong>Nome da mãe</strong></td>
                <td style="border: 1px solid #333; padding: 4px 8px;">&nbsp;</td>
            </tr>
        </table>

        <br>
        <div class="f11" style="line-height: 26pt">
            São Luís - MA, {{ (new \MasterTag\DataHora())->dataCompletaExt() }}.
        </div>

        <br><br><br>
        <div class="f11" style="line-height: 16pt; text-align: center">
            <hr style="width: 10cm; margin-top: 5px; margin-left: 24%; border: none; border-top: 1px solid #333">
            {{ $dados['dados_colaborador']->Curriculo->nome }}<br>
            <span style="font-size: 9pt;">COLABORADOR(A)</span>
        </div>
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
