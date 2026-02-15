@extends('layouts.mail.layout')

@section('conteudo')
<h2>MUDANÇA DE CARGO</h2>

@if(isset($dados['tipo']))
@if($dados['tipo'] === 'criacao')
<p><strong>Uma nova mudança de cargo foi solicitada e aguarda sua confirmação.</strong></p>
@elseif($dados['tipo'] === 'nova solicitação')
<p><strong>Uma nova mudança de cargo aguarda sua aprovação.</strong></p>
@elseif($dados['tipo'] === 'atualização')
<p>A mudança de cargo foi <strong>{{ $dados['status'] ?? 'atualizada' }}</strong> pela etapa: <strong>{{ $dados['etapa'] }}</strong></p>
@endif
@else
<p><strong>Aguardando aprovação ({{ $dados['etapa'] ?? 'N/A' }})</strong></p>
@endif

<table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
    <tr>
        <td style="padding: 8px; border: 1px solid #ddd; background-color: #f9f9f9;"><strong>Código:</strong></td>
        <td style="padding: 8px; border: 1px solid #ddd;">{{ $dados['mudanca_cargo_id'] }}</td>
    </tr>
    <tr>
        <td style="padding: 8px; border: 1px solid #ddd; background-color: #f9f9f9;"><strong>Colaborador:</strong></td>
        <td style="padding: 8px; border: 1px solid #ddd;">{{ $dados['colaborador'] ?? 'N/A' }}</td>
    </tr>
    @if(isset($dados['data_solicitacao']))
    <tr>
        <td style="padding: 8px; border: 1px solid #ddd; background-color: #f9f9f9;"><strong>Data da Solicitação:</strong></td>
        <td style="padding: 8px; border: 1px solid #ddd;">{{ $dados['data_solicitacao'] }}</td>
    </tr>
    @endif
</table>

@if(isset($dados['mudancas']))
<h3 style="margin-top: 25px; color: #072433;">Mudanças Solicitadas (DE → PARA)</h3>
<table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
    <thead>
        <tr style="background-color: #072433; color: white;">
            <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Item</th>
            <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">DE</th>
            <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">PARA</th>
        </tr>
    </thead>
    <tbody>
        @if($dados['mudancas']['centro_custo']['mudou'])
        <tr>
            <td style="padding: 8px; border: 1px solid #ddd; background-color: #f9f9f9;"><strong>Centro de Custo</strong></td>
            <td style="padding: 8px; border: 1px solid #ddd;">{{ $dados['mudancas']['centro_custo']['de'] }}</td>
            <td style="padding: 8px; border: 1px solid #ddd; background-color: #fffacd;">{{ $dados['mudancas']['centro_custo']['para'] }}</td>
        </tr>
        @endif

        @if($dados['mudancas']['cargo']['mudou'])
        <tr>
            <td style="padding: 8px; border: 1px solid #ddd; background-color: #f9f9f9;"><strong>Cargo</strong></td>
            <td style="padding: 8px; border: 1px solid #ddd;">{{ $dados['mudancas']['cargo']['de'] }}</td>
            <td style="padding: 8px; border: 1px solid #ddd; background-color: #fffacd;">{{ $dados['mudancas']['cargo']['para'] }}</td>
        </tr>
        @endif

        @if($dados['mudancas']['funcao']['mudou'])
        <tr>
            <td style="padding: 8px; border: 1px solid #ddd; background-color: #f9f9f9;"><strong>Função</strong></td>
            <td style="padding: 8px; border: 1px solid #ddd;">{{ $dados['mudancas']['funcao']['de'] }}</td>
            <td style="padding: 8px; border: 1px solid #ddd; background-color: #fffacd;">{{ $dados['mudancas']['funcao']['para'] }}</td>
        </tr>
        @endif

        @if($dados['mudancas']['salario']['mudou'])
        <tr>
            <td style="padding: 8px; border: 1px solid #ddd; background-color: #f9f9f9;"><strong>Salário</strong></td>
            <td style="padding: 8px; border: 1px solid #ddd;" colspan="2">
                <span style="color: #ff6b6b;">✓ Haverá mudança de salário</span>
            </td>
        </tr>
        @endif
    </tbody>
</table>
@endif

@if(isset($dados['status_aprovacao_gestor']) || isset($dados['status_aprovacao_extra']) || isset($dados['status_aprovacao_rh']))
<h3 style="margin-top: 25px; color: #072433;">Status das Aprovações</h3>
<table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
    @if(isset($dados['status_aprovacao_gestor']) && $dados['status_aprovacao_gestor'])
    <tr>
        <td style="padding: 8px; border: 1px solid #ddd; background-color: #f9f9f9;"><strong>Status Gestor:</strong></td>
        <td style="padding: 8px; border: 1px solid #ddd;">
            <span style="color: {{ $dados['status_aprovacao_gestor'] === 'aprovado' ? 'green' : 'red' }}; font-weight: bold;">
                {{ ucfirst($dados['status_aprovacao_gestor']) }}
            </span>
        </td>
    </tr>
    @endif
    @if(isset($dados['status_aprovacao_extra']) && $dados['status_aprovacao_extra'])
    <tr>
        <td style="padding: 8px; border: 1px solid #ddd; background-color: #f9f9f9;"><strong>Status Aprovação Extra:</strong></td>
        <td style="padding: 8px; border: 1px solid #ddd;">
            <span style="color: {{ $dados['status_aprovacao_extra'] === 'aprovado' ? 'green' : 'red' }}; font-weight: bold;">
                {{ ucfirst($dados['status_aprovacao_extra']) }}
            </span>
        </td>
    </tr>
    @endif
    @if(isset($dados['status_aprovacao_rh']) && $dados['status_aprovacao_rh'])
    <tr>
        <td style="padding: 8px; border: 1px solid #ddd; background-color: #f9f9f9;"><strong>Status RH:</strong></td>
        <td style="padding: 8px; border: 1px solid #ddd;">
            <span style="color: {{ $dados['status_aprovacao_rh'] === 'aprovado' ? 'green' : 'red' }}; font-weight: bold;">
                {{ ucfirst($dados['status_aprovacao_rh']) }}
            </span>
        </td>
    </tr>
    @endif
</table>
@endif

<p style="margin-top: 20px;">
    <a href="{{ $dados['link'] ?? '#' }}" style="background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; display: inline-block; border-radius: 4px;">
        Acessar Sistema
    </a>
</p>
@endsection