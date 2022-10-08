@extends('layouts.mail.layout')
@section('titulo', $subject)
@section('conteudo')

    <table border="0" align="center" cellpadding="0" width="97%" style="width: 100%;padding: 25px;">
        <tr>
            <td style="text-align: justify">

                Olá, <strong>{{ $dados['usuario']->nome }}</strong>,<br><br>
                @foreach($dados['vencimentos'] as $vencimento)

                    A avaliação de 90 dias do colaborador <strong>{{$vencimento['colaborador']}}</strong> está próximo,
                    o prazo de vencimento é: <strong>{{$vencimento['prazo_vencido']}}</strong>

                    <br><br>
                @endforeach

                MyBP - Business Partners Serviços Empresariais
                <br><br>

            </td>
        </tr>
    </table>
@endsection
