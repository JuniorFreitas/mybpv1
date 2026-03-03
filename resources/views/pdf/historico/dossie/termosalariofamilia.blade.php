@extends('layouts.pdf_filial')
@section('title','Relatorio de ponto')
@section('conteudo')
    <div style="margin-left: 9px">
        @include('layouts.cabecalioFilialEmpresaJob')
    </div>
    <div style="margin-left: 9px; width: 95%">
        <p class="f12"
           style="text-align: center; margin-bottom: 1cm; margin-top: 0.5cm; text-transform: uppercase">
            <b>TERMO DE RESPONSABILIDADE</b><br>
            (Concessão de Salário Família - Portaria nº MPAS - 3.040/82)
        </p>
        <p class="f11" style="line-height: 18pt;text-align: justify">
            EMPRESA: <br><br>
            @include('layouts.dadosEmpresa')
        </p>
        <p class="f11" style="line-height: 18pt;text-align: justify">
            EMPREGADO: {{$dados['dados_colaborador']->Curriculo->nome}}
            <br>
            RG: {{$dados['dados_colaborador']->rg ?? ''}}
            <br>
            CTPS: {{isset($dados['dados_colaborador']->Admissao->DadosAdmissoes) ? $dados['dados_colaborador']->Admissao->DadosAdmissoes->ctps_numero : ''}}
            <br>
            <br>
            NOME DO FILHO:
            <br>
            DATA DE NASCIMENTO:
        </p>
        <p class="f11" style="line-height: 18pt;text-align: justify">
            Pelo presente TERMO DE RESPONSABILIDADE, declaro estar ciente de que deverei comunicar de imediato a
            ocorrência
            dos seguintes fatos, que determinam a perda do direito ao salário família.
        </p>
        <p class="f11" style="text-transform: uppercase;line-height: 18pt;text-align: justify">
            - ÓBITO DO FILHO;<br>
            - CESSAÇÃO INVALIDEZ DE FILHO INVÁLIDO;<br>
            - SENTENÇA JUDICIAL PARA PAGAMENTO A OUTREM.<br>
        </p>
        <p class="f11" style="line-height: 18pt;text-align: justify">
            {{--        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;--}}
            Estou ciente, ainda, de que a falta de cumprimento do compromisso ora assumido, além de obrigar a devolução
            das
            importâncias recebidas indevidamente, sujeitar-me-á às penalidades previstas no artigo 171 do código penal e
            à
            rescisão do contrato de trabalho, por justa causa, nos termos do artigo 482 da CLT.
        </p>
        <br> <br>
        <div class="f11" style="line-height: 26pt">
            São Luís - MA, {{ (new \MasterTag\DataHora())->dataCompletaExt() }}.
            <br>
            <br>
        </div>
        <div class="f11" style="line-height: 26pt;text-align: center">
            {{--        <hr style="width: 10cm; margin-left: 24%; border:none; border-top: 1px solid #333">--}}
            {{--        {{$dados['dados_colaborador']->User->DadosEmpresa->razao_social}}--}}
            <br>
            <hr style="width: 10cm; margin-top: 5px;  margin-left: 24%;  border:none; border-top: 1px solid #333">
            {{$dados['dados_colaborador']->Curriculo->nome}}
        </div>

        <div style="position:fixed; bottom: 35px">
            @include('layouts.rodapePdfFilialJob')
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
