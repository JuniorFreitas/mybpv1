@extends('layouts.pdf_filial')
@section('title','Relatorio de ponto')
@section('conteudo')
    @include('layouts.cabecalioFilialEmpresaJob')
    <h2 style="text-align: center">
        RELATÓRIO SINTÉTICO DE PONTO {{ mb_strtoupper($dados['periodo']) }}
    </h2>
    <table class="dados3" style="width: 98%;">
        <thead>
        <tr>
            <td style="text-transform: uppercase;">Colaborador</td>
            <td style="text-transform: uppercase; text-align: center">Faltas</td>
            <td style="text-transform: uppercase; text-align: center">Horas Normais</td>
            <td style="text-transform: uppercase; text-align: center">Horas Noturnas</td>
            <td style="text-transform: uppercase; text-align: center">Horas Extras</td>
            <td style="text-transform: uppercase; text-align: center">Horas Negativas</td>
            <td style="text-transform: uppercase; text-align: center">Saldo</td>
            <td style="text-transform: uppercase; text-align: center">Dias Trabalhados</td>
        </tr>

        </thead>
        <tbody>
        @foreach($dados['dados_ponto'] as $ponto)
            <tr>
                <td>{{$ponto['funcionario']->nome}}</td>
                <td style="text-align: center">{{$ponto['total_faltas']}}</td>
                <td style="text-align: center">{{$ponto['total_horas_normais']}}</td>
                <td style="text-align: center">{{$ponto['total_horas_noturnas']}}</td>
                <td style="text-align: center">{{$ponto['total_horas_extra']}}</td>
                <td style="text-align: center">{{$ponto['total_horas_negativas']}}</td>
                <td style="text-align: center">{{$ponto['saldo_horas']}}</td>
                <td style="text-align: center">{{$ponto['total_dias_trabalhados']}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @include('layouts.rodapePdfFilialJob')
@stop
