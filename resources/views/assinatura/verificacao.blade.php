@extends('assinatura.layout')

@section('title', 'Verificação de assinatura digital')

@section('content')
<div class="card assin-card">
    <div class="card-header">
        <h5 class="mb-0">Verificação de assinatura</h5>
        <p class="assin-subtitle">Consulta pública de autenticidade do documento</p>
    </div>
    <div class="card-body">
        <div class="text-center mb-3">
            <div class="assin-status-icon {{ $valido ? 'assin-status-ok' : 'assin-status-warn' }}">
                {{ $valido ? 'OK' : '!' }}
            </div>
            <p class="lead {{ $valido ? 'text-success' : 'text-danger' }} mb-2">{{ $mensagem }}</p>
        </div>

        @if($valido && $documento && $signatario)
            <div class="assin-origin mb-3">
                Documento validado com evidências registradas para a empresa <strong>{{ $empresa->razao_social ?? $empresa->nome_fantasia ?? $empresa->apelido }}</strong>.
            </div>

            <div class="mb-2">
                <span class="assin-chip">{{ \App\Models\DocumentoParaAssinatura::labelTipoDocumento($documento->tipo_documento) }}</span>
                <span class="assin-chip">Status: {{ $documento->status }}</span>
            </div>

            <div class="assin-section mb-3">
                <div class="assin-section-title">Signatário</div>
                <ul class="list-unstyled mb-0">
                    <li><strong>Nome:</strong> {{ $signatario->nome }}</li>
                    <li><strong>E-mail:</strong> {{ $signatario->email }}</li>
                    @if($signatario->cpf)
                        <li><strong>CPF:</strong> {{ $cpfExibicao }}</li>
                    @endif
                    @if($signatario->ip)
                        <li><strong>IP:</strong> {{ $ipExibicao }}</li>
                    @endif
                    @if($signatario->geolocalizacao && is_array($signatario->geolocalizacao))
                        @php
                            $geo = $signatario->geolocalizacao;
                            $partes = array_filter([$geo['city'] ?? '', $geo['regionName'] ?? '', $geo['country'] ?? '']);
                            $localTexto = $partes !== [] ? implode(', ', $partes) : '';
                        @endphp
                        @if($localTexto)
                            <li><strong>Local da assinatura (por IP):</strong> {{ $localTexto }}</li>
                        @endif
                    @endif
                    @if($signatario->data_assinatura_utc)
                        @php
                            $raw = $signatario->getRawOriginal('data_assinatura_utc');
                            $dataBrasilia = $raw ? \Carbon\Carbon::parse($raw, 'UTC')->setTimezone(config('app.timezone', 'America/Sao_Paulo')) : null;
                        @endphp
                        <li><strong>Data da assinatura (UTC):</strong> {{ $signatario->data_assinatura_utc }}</li>
                        @if($dataBrasilia)
                            <li><strong>Data da assinatura (Horário de Brasília):</strong> {{ $dataBrasilia->format('d/m/Y H:i:s') }}</li>
                        @endif
                    @endif
                    @if($signatario->consentimento_em)
                        @php
                            $consentRaw = $signatario->getRawOriginal('consentimento_em');
                            $consentBrasilia = $consentRaw ? \Carbon\Carbon::parse($consentRaw, 'UTC')->setTimezone(config('app.timezone', 'America/Sao_Paulo')) : null;
                        @endphp
                        <li><strong>Consentimento registrado (UTC):</strong> {{ $signatario->consentimento_em }}</li>
                        @if($consentBrasilia)
                            <li><strong>Consentimento registrado (Horário de Brasília):</strong> {{ $consentBrasilia->format('d/m/Y H:i:s') }}</li>
                        @endif
                    @endif
                </ul>
            </div>

            <div class="assin-section mb-3">
                <div class="assin-section-title">Evidências técnicas</div>
                <ul class="list-unstyled mb-0">
                    @if($documento->hash_sha256)
                        <li><strong>Hash do documento (SHA-256):</strong> {{ $documento->hash_sha256 }}</li>
                    @endif
                    @if($signatario->hash_evidencia)
                        <li><strong>Hash de evidências (SHA-256):</strong> {{ $signatario->hash_evidencia }}</li>
                    @endif
                </ul>
            </div>

            @if(!empty($historico))
                <div class="assin-section">
                    <div class="assin-section-title">Histórico</div>
                    <p class="small text-muted mb-2">Data e horários em Horário de Brasília (GMT-3).</p>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>Data</th>
                                    <th>Hora</th>
                                    <th>Descrição</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($historico as $ev)
                                    <tr>
                                        <td>{{ $ev['data_formatada'] }}</td>
                                        <td>{{ $ev['hora'] }}</td>
                                        <td>{{ $ev['descricao'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="small text-muted mt-2">Documento gerado eletronicamente. Validade jurídica conforme Lei 14.063/2020 e MP 2.200-2/2001.</p>
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
