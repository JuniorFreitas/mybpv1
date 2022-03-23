@extends('layouts.pdf')
@section('title','Termo de Salário Família')
@section('empresa')
    @include('layouts.cabecalioEmpresaModelo')
@endsection
@section('conteudo')
    <p class="f12"
       style="text-align: center; margin-bottom: 1cm; margin-top: 0.5cm; text-transform: uppercase">
        <b>TERMO DE RESPONSABILIDADE</b><br>
        (Concessão de Salário Família - Portaria nº MPAS - 3.040/82)
    </p>
    <p class="f11">
        EMPRESA: <br><br>

        @include('layouts.dadosEmpresa')

    </p><br>
    <p class="f11" style="">
        EMPREGADO: {{$dados->nome}}
        <br>
        RG: {{$dados->rg ?? ''}}
        <br>
        CTPS: {{isset($dados->FeedBack->Admissao->DadosAdmissoes) ? $dados->FeedBack->Admissao->DadosAdmissoes->ctps_numero : ''}}
        <br>
        <br>
        <br>
        NOME DO FILHO:
        <br>
        DATA DE NASCIMENTO:
        <br>
    </p>
    <br>
    <p class="f11">
        Pelo presente TERMO DE RESPONSABILIDADE, declaro estar ciente de que deverei comunicar de imediato a ocorrência
        dos seguintes fatos, que determinam a perda do direito ao salário família.
        <br><br>
    </p>
    <p class="f10" style="text-transform: uppercase">
        - ÓBITO DO FILHO;<br>
        - CESSAÇÃO INVALIDEZ DE FILHO INVÁLIDO;<br>
        - SENTENÇA JUDICIAL PARA PAGAMENTO A OUTREM.<br>
    </p>
    <br>
    <p class="f11" style="line-height: 22pt;text-align: justify">
        {{--        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;--}}
        Estou ciente, ainda, de que a falta de cumprimento do compromisso ora assumido, além de obrigar a devolução das
        importâncias recebidas indevidamente, sujeitar-me-á às penalidades previstas no artigo 171 do código penal e à
        rescisão do contrato de trabalho, por justa causa, nos termos do artigo 482 da CLT.
    </p>
    <br>
    <br>
    <div class="f11" style="line-height: 26pt">
        {{ (new \MasterTag\DataHora())->dataCompletaExt() }}.
        <br>
        São Luís, MA.
        <br>
        <br>
    </div>
    <div class="f11" style="line-height: 26pt;text-align: center">
        {{--        <hr style="width: 10cm; margin-left: 24%; border:none; border-top: 1px solid #333">--}}
        {{--        {{$dados->User->DadosEmpresa->razao_social}}--}}
        <br>
        <hr style="width: 10cm; margin-top: 5px;  margin-left: 24%;  border:none; border-top: 1px solid #333">
        {{$dados->nome}}
    </div>

    <div class="footer">
        <p class="obs">
            Esse documento foi gerado automaticamente pelo usuário {{ auth()->user()->nome }}: <br>
            Sistema Integrado MYBP em {{ (new \MasterTag\DataHora())->dataCompleta() }}
            às {{ (new \MasterTag\DataHora())->horaCompleta() }}.
        </p>
        <div>
            <hr style="border:none; border-top: 1px solid #999">
            {{$dados->User->DadosEmpresa->razao_social}}<br>
            CNPJ: {{$dados->User->DadosEmpresa->cnpj}} <br>
            {{$dados->User->DadosEmpresa->endereco_completo}}
        </div>
    </div>
@endsection

@push('style')
    <style type="text/css">
        .footer {
            position: absolute;
            bottom: 0px;
            font-size: 8.4pt;
            /*width: 10cm;*/
        }

        .f14 {
            font-size: 14pt;
        }

        .f11 {
            font-size: 11pt;
        }

        .f12 {
            font-size: 12pt;
        }

        .f10 {
            font-size: 10pt;
        }

        .obs {
            font-size: 8.4pt;
            color: #444444;
            margin-bottom: 10px;
        }

    </style>
@endpush
