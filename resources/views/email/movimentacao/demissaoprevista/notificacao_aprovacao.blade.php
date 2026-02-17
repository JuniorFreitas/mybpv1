@extends('layouts.mail.layout')
@section('titulo', 'Demissão Prevista')
@section('conteudo')
<table border="0" align="center" cellpadding="0" width="97%" style="width: 100%;padding: 25px;">
    <tr>
        <td style="text-align: justify">
            Olá, <strong>{{ $dados['nome_para'] }}</strong>!<br><br>

            @if(isset($dados['tipo']) && $dados['tipo'] === 'criacao')
            <strong>{{ $dados['nome_de'] }}</strong> criou uma nova solicitação de <strong>Demissão em Planejamento Movimentação</strong> que está <strong>pendente de aprovação</strong> na etapa de aprovação do gestor.<br>
            @elseif(isset($dados['tipo']) && $dados['tipo'] === 'atualização')
            <strong>{{ $dados['nome_de'] }}</strong>  atualizou o status de <strong>Demissão em Planejamento Movimentação</strong> na etapa <strong>{{ $dados['etapa'] }}</strong>.<br>
            @if(isset($dados['status_aprovacao']))
            Status: <strong>{{ ucfirst($dados['status_aprovacao']) }}</strong><br>
            @endif
            @else
            Uma <strong>Demissão em Planejamento Movimentação</strong> está <strong>pendente de aprovação</strong> na etapa <strong>{{ $dados['etapa'] }}</strong>.<br>
            Aprovada por: <strong>{{ $dados['nome_de'] }}</strong><br>
            @endif

            <br>
            <strong>Detalhes da Solicitação:</strong><br>
            Cód: <strong>{{ $dados['demissao_id'] }}</strong><br>
            Colaborador: <strong>{{ $dados['colaborador'] }}</strong><br>
            Etapa: <strong>{{ $dados['etapa'] }}</strong><br>

            <br><br>
            @if(isset($dados['tipo']) && $dados['tipo'] === 'atualização')
            Para visualizar, acesse o sistema <a href="{{ route('g.movimentacao.index') }}" style="color: #007bff; text-decoration: none; font-weight: bold;">clicando aqui</a>.
            @else
            Para visualizar e aprovar, acesse o sistema <a href="{{ route('g.movimentacao.index') }}" style="color: #007bff; text-decoration: none; font-weight: bold;">clicando aqui</a>.
            @endif
        </td>
    </tr>
</table>
@endsection
