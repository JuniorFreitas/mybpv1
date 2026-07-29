@extends('layouts.pdf_filial')
@section('title','CONCESSÃO OU DISPENSA DE ADIANTAMENTO SALARIAL')
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
            <strong>CONCESSÃO OU DISPENSA DE ADIANTAMENTO SALARIAL</strong><br>
        </p>

        <p class="f11" style="line-height: 18pt; text-align: justify">
            Eu, <strong>{{ $dados['dados_colaborador']->Curriculo->nome }}</strong>,
            inscrito(a) no CPF sob o nº <strong>{{ $dados['dados_colaborador']->Curriculo->cpf ?? '' }}</strong>,
            declaro que é de meu interesse a opção abaixo referente ao adiantamento salarial correspondente a 40% do meu salário.
        </p>

        <p class="f11" style="line-height: 18pt;">
            Marque apenas uma opção:
            <br><br>
            (&nbsp;&nbsp;&nbsp;) SIM, desejo receber o adiantamento salarial de 40%.
            <br><br>
            (&nbsp;&nbsp;&nbsp;) NÃO, não desejo receber o adiantamento salarial de 40%.
        </p>

        <br><br>
        <div class="f11" style="line-height: 26pt">
            São Luís - MA, {{ (new \MasterTag\DataHora())->dataCompletaExt() }}.
            <br>
            <br>
        </div>

        <p class="f11" style="margin-top: 0.5cm;">
            Atenciosamente,
        </p>

        <br><br><br>
        <div class="f11" style="line-height: 16pt; text-align: center">
            <hr style="width: 10cm; margin-top: 5px; margin-left: 24%; border:none; border-top: 1px solid #333">
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
