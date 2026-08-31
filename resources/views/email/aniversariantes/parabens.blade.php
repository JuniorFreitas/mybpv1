@extends('layouts.mail.layout')
@section('titulo', 'Parabéns')
@section('conteudo')

    <table border="0" align="center" cellpadding="0" width="97%" style="width: 100%;padding: 25px;">
        <tr>
            <td style="text-align: justify">
                @if(!empty($dados['mensagem']))
                    {!! $dados['mensagem'] !!}
                @else
                    Hoje se completa mais um ano da vida de alguém muito importante e valoroso,
                    você {{ $dados['nome'] }}!<br><br>
                    Todos nós lhe desejamos um dia muito feliz, que possa celebrar junto da família e amigos e que cumpra
                    muitos anos preenchidos de amor, saúde e paz.
                    <br>Muitos parabéns!<br>
                @endif
            </td>
        </tr>
    </table>
@endsection
