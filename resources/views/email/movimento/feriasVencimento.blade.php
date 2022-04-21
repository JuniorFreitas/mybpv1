@extends('layouts.mail.layout')
@section('titulo', $subject)
@section('conteudo')

    <table border="0" cellpadding="0" width="97%" style="width: 97%;">
        <tr>
            <td style="text-align: justify">
                Olá, <strong>{{ $dados['usuario']->nome }}</strong>,<br><br>
                @foreach($dados['vencimento'] as $vencimento)
                    A férias do colaborador <strong>{{$vencimento->Colaborador->nome}}</strong> está próximo do vencimento,
                    sendo a última data no dia: {{$vencimento->UltimaData}}
                    <br><br>
                @endforeach
            </td>
        </tr>
    </table>
@endsection
