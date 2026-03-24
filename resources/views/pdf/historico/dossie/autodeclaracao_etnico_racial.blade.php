@extends('layouts.pdf_filial')
@section('title','AUTODECLARAÇÃO ÉTNICO-RACIAL')
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
            <strong>AUTODECLARAÇÃO ÉTNICO-RACIAL</strong><br>
        </p>
        <p class="f11" style="line-height: 18pt;">
            Eu, <strong>{{$dados['dados_colaborador']->Curriculo->nome}}</strong>,
            inscrito no CPF sob o nº <strong>{{$dados['dados_colaborador']->Curriculo->cpf ?? ''}}</strong>,
            AUTODECLARO, sob as penas da lei, minha raça/etnia sendo:
            <br>
            <br>
            [&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;] Branca <br>
            [&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;] Preta <br>
            [&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;] Parda <br>
            [&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;] Amarela <br>
            [&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;] Indígena <br>

            <br>
        </p>
        <p class="f11" style="line-height: 18pt;text-align: justify">
            Esta autodeclaração atende a exigência do art. 39, § 8º, da Lei nº 12.288/2010, alterado pela Lei nº
            14.553/2023 e da Portaria MTE nº 3.784/2023, que obriga a prestação da informação nas inclusões, alterações
            ou retificações cadastrais dos trabalhadores ocorridas a partir de 1º de janeiro de 2024, respeitando o
            critério de autodeclaração do trabalhador, em conformidade com a classificação utilizada pelo Instituto
            Brasileiro de Geografia e Estatística - IBGE.
            <br><br>
            Por ser expressão da verdade, firmo e assino a presente para que a mesma produza seus efeitos legais e de
            direito.
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
