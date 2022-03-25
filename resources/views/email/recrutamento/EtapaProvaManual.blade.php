@extends('layouts.mail.layout')
@section('titulo', 'E-mail de proxima')
@section('conteudo')

    <table border="0" cellpadding="0" width="97%" style="width: 97%;">
        <tr>

            <td style="text-align: justify">
                @if($dados['etapa'] != 'Aviso Recesso')
                    Olá, <strong>{{ $dados['nome'] }}</strong> Parabéns!<br><br>

                    {{ $dados['mensagem'] }} <br><br>

                    Sucesso e esperamos vê-lo em breve!<br><br>

                @endif


                <br><br>
            </td>
        </tr>
    </table>
@endsection
