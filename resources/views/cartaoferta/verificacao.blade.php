@extends('assinatura.layout')

@section('title', 'Verificação da Carta Oferta')
@section('selo', 'Verificação')

@section('content')
    <div class="card assin-card">
        <div class="card-header">
            <h5 class="mb-0">Verificação de autenticidade</h5>
            <p class="assin-subtitle">Carta Oferta — aceite eletrônico</p>
        </div>
        <div class="card-body">
            @if(!$consultou)
                <p class="text-muted mb-0">
                    Aponte a câmera para o QR code do comprovante ou use o link de verificação recebido por e-mail.
                </p>
            @elseif(!$valido)
                <div class="text-center">
                    <div class="assin-status-icon assin-status-warn">!</div>
                    <h5>Registro não confirmado</h5>
                    <p class="text-muted mb-0">
                        Não encontramos um aceite válido para este código, ou os dados do comprovante não conferem
                        com o registro original.
                    </p>
                </div>
            @else
                @php
                    $cpf = preg_replace('/\D/', '', (string) $aceite->cpf);
                    $cpfMascarado = strlen($cpf) === 11 ? '***.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-**' : 'Não informado';
                    $ipMascarado = $aceite->ip ? preg_replace('/\.\d+\.\d+$/', '.***.***', $aceite->ip) : 'Não informado';
                    $geo = is_array($aceite->geolocalizacao) ? $aceite->geolocalizacao : [];
                    $local = implode(', ', array_filter([$geo['city'] ?? null, $geo['regionName'] ?? null, $geo['country'] ?? null]));
                @endphp
                <div class="text-center mb-3">
                    <div class="assin-status-icon assin-status-ok">✓</div>
                    <h5 class="mb-1">Registro autêntico</h5>
                    <p class="text-muted mb-0">Os dados abaixo conferem com o registro original e não foram alterados.</p>
                </div>

                <div class="assin-origin mb-3">
                    Carta Oferta de <strong>{{ $empresaNome ?: 'Não informado' }}</strong> —
                    <strong>{{ $aceite->aceito() ? 'ACEITA' : 'RECUSADA' }}</strong> eletronicamente pelo candidato.
                </div>

                <div class="assin-section mb-3">
                    <div class="assin-section-title">Quem respondeu</div>
                    <ul class="list-unstyled mb-0">
                        <li><strong>Nome:</strong> {{ $aceite->nome }}</li>
                        <li><strong>CPF:</strong> {{ $cpfMascarado }}</li>
                        <li><strong>Data e hora (Brasília):</strong> {{ $aceite->data_decisao_br }}</li>
                        <li><strong>IP:</strong> {{ $ipMascarado }}</li>
                        @if($local !== '')
                            <li><strong>Local aproximado (por IP):</strong> {{ $local }}</li>
                        @endif
                    </ul>
                </div>

                <div class="assin-section mb-3">
                    <div class="assin-section-title">Evidências</div>
                    <ul class="list-unstyled mb-0" style="word-break: break-all;">
                        <li><strong>Código de verificação:</strong> {{ $aceite->codigo_verificacao }}</li>
                        <li><strong>Hash das evidências (SHA-256):</strong> {{ $aceite->hash_evidencia }}</li>
                        <li><strong>Hash do documento (SHA-256):</strong> {{ $aceite->proposta_hash ?: 'Não informado' }}</li>
                    </ul>
                </div>

                @if(count($historico))
                    <div class="assin-section">
                        <div class="assin-section-title">Histórico</div>
                        <ul class="list-unstyled mb-0">
                            @foreach($historico as $evento)
                                <li class="mb-1">
                                    <strong>{{ $evento['data_formatada'] }} {{ $evento['hora'] }}</strong> — {{ $evento['descricao'] }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection
