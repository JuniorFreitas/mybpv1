@extends('layouts.mail.layout')
@section('titulo', $assunto)
@section('conteudo')

    <table border="0" align="center" cellpadding="0" width="97%" style="width: 100%;padding: 25px;">
        <tr>

            <td style="text-align: justify">

                Olá, <strong>{{ $para->nome }}</strong>,<br><br>
                A tarefa <strong>{{$tarefa->titulo}}</strong>@if($tarefa->Lista) da lista <strong>{{$tarefa->Lista->titulo}}</strong>@endif deve ser entregue até {{$tarefa->DataHoraEntregaFormatada ?? 'a data definida'}}

                <br><br>
            </td>
        </tr>
    </table>
@endsection
