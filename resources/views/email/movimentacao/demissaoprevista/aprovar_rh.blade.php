@extends('layouts.mail.layout')
@section('titulo', 'Demissão Prevista')
@section('conteudo')
    <table border="0" align="center" cellpadding="0" width="97%" style="width: 100%;padding: 25px;">
        <tr>
            <td style="text-align: justify">
                Olá, <strong>{{ $dados['nome_para'] }}</strong>!<br><br>

                <strong>{{ $dados['nome_de'] }}</strong> atualizou o status de <strong>Demissão em Planejamento Movimentação</strong> na etapa de aprovação do RH.<br>
                Status: <strong>Aprovado</strong><br>

                <br>
                <strong>Detalhes da Solicitação:</strong><br>
                ID: <strong>{{ $dados['id'] }}</strong><br>
                Colaborador: <strong>{{ $dados['colaborador'] }}</strong><br>

                <br><br>
                Para visualizar, acesse o sistema <a href="{{ route('g.movimentacao.index') }}" style="color: #007bff; text-decoration: none; font-weight: bold;">clicando aqui</a>.

            </td>
        </tr>
    </table>
@endsection
