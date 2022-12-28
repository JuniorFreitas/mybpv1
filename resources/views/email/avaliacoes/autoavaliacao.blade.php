@extends('layouts.mail.layout')
@section('titulo', 'Auto Avaliação Concluída')
@section('conteudo')
    <table border="0" cellpadding="0" width="100%" style="width: 100%;">
        <tr>
            <td style="text-align: justify; padding: 30px;">
                Olá, <strong>{{ $dados['nome'] }}</strong>! Tudo bem?<br>
                Estamos informando que <strong>{{ $dados['funcionario'] }}</strong> concluiu a auto avaliação
                <strong>{{ $dados['avaliacao'] }}</strong>.
                <br><br>
                Você já pode fazer a avaliação deste(a) funcionário(a) no menu <strong>Minhas Avaliações</strong>.
            </td>
        </tr>
    </table>
@endsection
