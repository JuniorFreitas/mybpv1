@extends('layouts.mail.layout')
@section('titulo', $assunto)
@section('conteudo')

    <table border="0" align="center" cellpadding="0" width="97%" style="width: 100%;padding: 25px;">
        <tr>
            <td style="text-align: justify">

                Olá, <strong>{{ $para->nome }}</strong>,<br><br>
                @if($acao=='add')
                    <strong>{{$de->nome}}</strong> adicionou você na tarefa <strong>{{$tarefa->titulo}}</strong> da lista <strong>{{$tarefa->Lista->titulo}}</strong> <br><br>
                    Acesse agora: <a href="{{env('APP_URL')}}">{{env('APP_URL')}}</a><br><br>
                @endif

                @if($acao=='remove')
                    <strong>{{$de->nome}}</strong> removeu você da tarefa <strong>{{$tarefa->titulo}}</strong> da lista <strong>{{$tarefa->Lista->titulo}}</strong> <br><br>
                @endif


                <br><br>
            </td>
        </tr>
    </table>
@endsection
