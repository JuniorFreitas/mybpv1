@extends('layouts.mail.layout')
@section('titulo', $assunto)
@section('conteudo')

    <table border="0" cellpadding="0" width="97%" style="width: 97%;">
        <tr>
            <td>
                <img src="https://sgibpse.com.br/imagens/bepinhas/branca_2.png" alt="Bepinha">
            </td>
            <td style="text-align: justify">

                Olá, <strong>{{ $para->nome }}</strong>,<br><br>
                A tarefa <strong>{{$tarefa->titulo}}</strong> da lista <strong>{{$tarefa->Lista->titulo}}</strong> deve ser entregue até {{$tarefa->DataHoraEntregaFormatada}}
                <br><br>

                Abraços<br><br>
                BPSE-Business Partners Serviços Empresariais

                <br><br>
            </td>
        </tr>
    </table>
@endsection
