@extends('pdf.treinamento.carteira.layout_carteira')
@section('titulo', 'Carteiras')
@section('conteudo')

    @if($tipo == 'treinamento')
        @foreach($grupos_carteira as $grupo)
            @include($grupo['view'], ['treinamentos' => $grupo['itens']])
        @endforeach
    @endif

    @if($tipo == 'bloqueio')
        @foreach($grupos_etiqueta as $grupo)
            @include($grupo['view'], ['treinamentos' => $grupo['itens']])
        @endforeach
    @endif

    @if($tipo == 'treinamento_bloqueio')
        @foreach($grupos_carteira as $grupo)
            @include($grupo['view'], ['treinamentos' => $grupo['itens']])
        @endforeach
        @foreach($grupos_etiqueta as $index => $grupo)
            @include($grupo['view'], ['treinamentos' => $grupo['itens'], 'aposTreinamento' => $index === 0])
        @endforeach
    @endif

@endsection
