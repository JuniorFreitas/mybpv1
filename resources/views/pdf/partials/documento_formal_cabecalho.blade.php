@php
    $empresaPdf = is_array($dados_empresa ?? null)
        ? $dados_empresa
        : (json_decode(json_encode($dados_empresa ?? []), true) ?: []);
    $razaoPdf = (string) ($empresaPdf['razao_social'] ?? ($empresaPdf['nome_fantasia'] ?? ''));
    $logoPdf = $empresaPdf['logo'] ?? null;
    $logoCompacto = (int) ($empresaPdf['empresa_id'] ?? 0) === 63122;
@endphp
<div class="cabecalho">
    @if(!empty($logoPdf))
        <div class="cabecalho__logo {{ $logoCompacto ? 'cabecalho__logo--compact' : '' }}">
            <img src="{{ $logoPdf }}" alt="{{ $razaoPdf }}">
        </div>
    @endif
    @if($razaoPdf !== '')
        <div class="cabecalho__razao">{{ $razaoPdf }}</div>
    @endif
    @if(!empty($empresaPdf['cnpj']) || !empty($empresaPdf['endereco_completo']))
        <div class="cabecalho__meta">
            @if(!empty($empresaPdf['cnpj']))
                CNPJ: {{ $empresaPdf['cnpj'] }}
            @endif
            @if(!empty($empresaPdf['cnpj']) && !empty($empresaPdf['endereco_completo']))
                <br>
            @endif
            @if(!empty($empresaPdf['endereco_completo']))
                {{ $empresaPdf['endereco_completo'] }}
            @endif
        </div>
    @endif
</div>
