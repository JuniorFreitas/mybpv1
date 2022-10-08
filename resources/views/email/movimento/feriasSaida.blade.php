@extends('layouts.mail.layout')
@section('titulo', $subject)
@section('conteudo')

    <table border="0" align="center" cellpadding="0" width="97%" style="width: 100%;padding: 25px;">
        <tr>
            <td style="text-align: justify">
                Olá, <strong>{{ $dados['usuario']->nome }}</strong>,<br><br>
                @foreach($dados['vencimento'] as $vencimento)
                    A férias do colaborador <strong>{{$vencimento->Colaborador->nome}}</strong> está próximo, a saída está
                    prevista para: {{$vencimento->DataSaida}}
                    <br><br>
                @endforeach
            </td>
        </tr>
    </table>
@endsection
