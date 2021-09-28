@extends('layouts.mail.layout')
@section('titulo', 'E-mail de proxima')
@section('conteudo')

    <table border="0" cellpadding="0" width="97%" style="width: 97%;">
        <tr>
            <td>
                <img src="https://sgibpse.com.br/imagens/bepinhas/branca_2.png" alt="Bepinha">
            </td>
            <td style="text-align: justify">
                @if($dados['etapa'] != 'Aviso Recesso')
                    Olá, <strong>{{ $dados['nome'] }}</strong> Parabéns!<br><br>

                    {{ $dados['mensagem'] }} <br><br>

                    Sucesso e esperamos vê-lo em breve!<br><br>
                @else
                    Olá, <strong>{{ $dados['nome'] }}</strong> o seu processo continua ativo, faremos pausa até 05 de
                    janeiro por conta do recesso de final de ano assim que retornarmos entraremos em contato sobre as
                    próximas etapas!<br><br>
                @endif
                Abraços<br><br>
                BPSE-Business Partners Serviços Empresariais

                <br><br>
            </td>
        </tr>
    </table>
@endsection
