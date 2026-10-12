@extends('layouts.mail.layout')
@section('titulo', 'Comprovante de envio de documentos')
@section('conteudo')
    @php
        $empresa = \App\Models\Cliente::withoutGlobalScopes()->find($dados['empresa_id']);
    @endphp
    <table border="0" align="center" cellpadding="0" width="97%" style="width: 100%;padding: 25px;">
        <tr>
            <td style="text-align: left">
                Prezado(a) <strong>{{ $dados['nome'] }}</strong>,<br><br>
                Recebemos seus documentos. Guarde este e-mail como comprovante.
                <br><br>
                <strong>Protocolo:</strong> {{ $dados['protocolo'] }}<br>
                <strong>Data e hora:</strong> {{ $dados['data_hora'] ?? 'Não informado' }} (horário de Brasília)<br>
                <strong>CPF:</strong> {{ $dados['cpf_mascarado'] ?? 'Não informado' }}<br>
                <strong>Endereço IP:</strong> {{ $dados['ip'] ?? 'Não informado' }}<br>
                <strong>Termo aceito:</strong> versão {{ $dados['termo_versao'] ?? 'Não informado' }}
                <br><br>
                <strong>Arquivos recebidos</strong>
                <table border="0" cellpadding="6" cellspacing="0" width="100%" style="font-size: 12px; border-collapse: collapse; margin-top: 8px;">
                    @forelse($dados['arquivos'] as $arquivo)
                        <tr>
                            <td style="border-top: 1px solid #dddddd;">
                                <strong>{{ $arquivo['documento'] ?? 'Não informado' }}</strong> — {{ $arquivo['nome'] ?? 'Não informado' }}<br>
                                <span style="font-family: monospace; font-size: 11px; color: #555555; word-break: break-all;">
                                    SHA-256: {{ $arquivo['sha256'] ?? 'Não informado' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td style="border-top: 1px solid #dddddd;">Nenhum arquivo anexado.</td></tr>
                    @endforelse
                </table>
                <br>
                O código SHA-256 identifica cada arquivo exatamente como foi recebido.
                Se você não reconhece este envio, procure o RH.
                <br><br>
                Atenciosamente,<br>
                Equipe {{ $empresa->razao_social ?? 'RH' }}
            </td>
        </tr>
    </table>
@endsection
