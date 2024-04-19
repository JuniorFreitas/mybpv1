@extends('layouts.pdf_filial')
@section('title','Relatorio de ponto')
@section('conteudo')
    <div style="margin-left: 9px">
        @include('layouts.cabecalioFilialEmpresaJob')
    </div>
    <div style="margin-left: 9px; width: 95%">
        <p class="f12"
           style="text-align: center; margin-bottom: 1cm; margin-top: 0.5cm; text-transform: uppercase">
            <b>DECLARAÇÃO DE ENCARGOS PARA IMPOSTO DE RENDA</b><br>
        </p>
        <p class="f11">
            EMPRESA: <br><br>

            @include('layouts.dadosEmpresa')

        </p><br>
        <p class="f11" style="line-height: 18pt;">
            EMPREGADO: {{$dados['dados_colaborador']->Curriculo->nome}}
            <br>
            CPF: {{$dados['dados_colaborador']->Curriculo->cpf ?? ''}}
            <br>
            CTPS: {{isset($dados['dados_colaborador']->Admissao->DadosAdmissoes) ? $dados['dados_colaborador']->Admissao->DadosAdmissoes->ctps_numero : ''}}
            <br>
        </p>
        <p class="f11" style="line-height: 18pt;text-align: justify">
            Em obediência à legislação do Imposto de Renda, declaro pela presente que tenho como encargo de família as
            pessoas abaixo relacionadas.
        </p>
        <p class="f10" style="text-transform: uppercase;line-height: 18pt;">
            NOME DO DEPENDENTE:<br>
            PARENTESCO:<br>
            NASCIMENTO:<br>
        </p>
        <p class="f11" style="line-height: 18pt;text-align: justify">
            Declaro sob as penas da lei, que as informações aqui prestadas são verdadeiras e da minha inteira
            responsabilidade, não cabendo a V.S.(s) _____________________________________________________ qualquer
            responsabilidade
            perante a fiscalização.
        </p>
        <br><br>
        <div class="f11" style="line-height: 26pt">
            São Luís - MA, {{ (new \MasterTag\DataHora())->dataCompletaExt() }}.
            <br>
            <br>
        </div>
        <div class="f11" style="line-height: 16pt;text-align: center">
            <br>
            <hr style="width: 10cm; margin-top: 5px;  margin-left: 24%;   border:none; border-top: 1px solid #333">
            {{$dados['dados_colaborador']->Curriculo->nome}}<br>
            {{$dados['dados_colaborador']->Admissao->cargo}}
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
