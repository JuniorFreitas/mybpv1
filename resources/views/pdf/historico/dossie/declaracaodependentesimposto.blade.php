@extends('layouts.pdf')
@section('title','DECLARAÇÃO DE ENCARGOS PARA IMPOSTO DE RENDA')
@section('empresa')
    @include('layouts.cabecalioEmpresa')
@endsection
@section('conteudo')
    <p class="f12"
       style="text-align: center; margin-bottom: 1cm; margin-top: 0.5cm; text-transform: uppercase">
        <b>DECLARAÇÃO DE ENCARGOS PARA IMPOSTO DE RENDA</b><br>
    </p>
    <p class="f11">
        EMPRESA: <br><br>

        @include('layouts.dadosEmpresa')

    </p><br>
    <p class="f11" style="">
        EMPREGADO: {{$dados->nome}}
        <br>
        CPF: {{$dados->cpf ?? ''}}
        <br>
        CTPS: {{isset($dados->FeedBack->Admissao->DadosAdmissoes) ? $dados->FeedBack->Admissao->DadosAdmissoes->ctps_numero : ''}}
        <br>
    </p>
    <br>
    <p class="f11">
        Em obediência à legislação do Imposto de Renda, declaro pela presente que tenho como encargo de família as
        pessoas abaixo relacionadas.
        <br><br>
    </p>
    <p class="f10" style="text-transform: uppercase">
        NOME DO DEPENDENTE:<br>
        PARENTESCO:<br>
        NASCIMENTO:<br>
    </p>
    <br>
    <p class="f11" style="line-height: 22pt;text-align: justify">
        Declaro sob as penas da lei, que as informações aqui prestadas são verdadeiras e da minha inteira
        responsabilidade, não cabendo a V.S.(s) _____________________________________________________ qualquer
        responsabilidade
        perante a fiscalização.
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
        <br>
        <hr style="width: 10cm; margin-top: 5px;  margin-left: 24%;  border:none; border-top: 1px solid #333">
        {{$dados->nome}}<br>
        {{$dados->FeedBack->Admissao->cargo}}
    </div>

    @include('layouts.rodapePdf')
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
