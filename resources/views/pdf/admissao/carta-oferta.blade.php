@extends('layouts.pdf')
@section('title', 'Carta Oferta')
@section('empresa')
    @include('layouts.cabecalioEmpresa')
@endsection
@section('conteudo')

    <p class="f14" style="text-align: center; font-weight: bold; margin-bottom: 1cm; text-transform: uppercase">
        CARTA OFERTA
    </p>

    <p class="f12" style="line-height: 22pt;">
        Prezado(a) <strong>{{ $cartaOferta->Curriculo->nome }}</strong>,
    </p>
    <br>
    <p class="f12" style="line-height: 22pt; text-align: justify">
        Conforme processo seletivo, temos o prazer de formalizar a presente carta oferta para o cargo de
        <strong>{{ $nomeCargo }}</strong>.
    </p>
    <br>
    <p class="f12" style="line-height: 22pt; text-align: justify">
        Ao assinar este documento, você declara estar ciente e de acordo com as condições apresentadas.
    </p>
    <br>
    <p class="f12" style="line-height: 22pt;">
        Data: {{ (new \MasterTag\DataHora())->dataCompletaExt() }}
    </p>
    <br>
    <div class="f12" style="line-height: 26pt; text-align: center">
        <hr style="width: 10cm; margin-left: 24%; border:none; border-top: 1px solid #333">
        @if($cartaOferta->empresa)
            {{ $cartaOferta->empresa->razao_social ?? $cartaOferta->empresa->nome_fantasia }}
        @endif
    </div>

@endsection
