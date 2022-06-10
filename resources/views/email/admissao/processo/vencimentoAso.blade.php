@extends('layouts.mail.layout')
@section('titulo', $subject)
@section('conteudo')

    <table border="0" cellpadding="0" width="97%" style="width: 97%;">
        <tr>
            <td style="text-align: justify">

                Olá, <strong>{{ $dados['usuario']->nome }}</strong>,<br><br>
                @foreach($dados['vencimentos'] as $vencimento)

                    O vencimento do ASO do(a) colaborador(a) <strong>{{$vencimento['colaborador']}}</strong> está próximo,
                    o prazo de vencimento é: <strong>{{$vencimento['data_vencimento']}}</strong>

                    <br><br>
                @endforeach

                MyBP
                <br><br>

            </td>
        </tr>
    </table>
@endsection
