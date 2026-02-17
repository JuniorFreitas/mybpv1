@extends('layouts.mail.layout')
@section('titulo', 'Demissão Prevista Criada')
@section('conteudo')
    <table border="0" align="center" cellpadding="0" width="97%" style="width: 100%;padding: 25px;">
        <tr>
            <td style="text-align: justify">
                Olá, <strong>{{ $dados['nome_para'] }}</strong>!<br><br>

                <strong>{{ $dados['nome_de'] }}</strong> criou uma nova solicitação de demissão prevista que está <strong>pendente de aprovação</strong> na etapa de aprovação do gestor.<br>

                <br>
                <strong>Detalhes da Solicitação:</strong><br>
                Cód: <strong>{{ $dados['demissao_id'] }}</strong><br>
                Colaborador: <strong>{{ $dados['colaborador'] }}</strong><br>
                Etapa: <strong>Gestor</strong><br>

                <br><br>
                Para visualizar e aprovar, acesse o sistema <a href="{{ route('g.movimentacao.index') }}" style="color: #007bff; text-decoration: none; font-weight: bold;">clicando aqui</a>.

            </td>
        </tr>
    </table>
@endsection
