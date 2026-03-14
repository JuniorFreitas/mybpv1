@php
    $user = auth()->user();
    $dadosEmpresa = $user?->DadosEmpresa;
    $clientesLogo = $user?->ClientesLogo ?? collect();
    $logoUrl = $clientesLogo->isNotEmpty() ? $clientesLogo->first()->urlThumb : null;
    $logoBase64 = null;
    if ($logoUrl) {
        try {
            $logoBase64 = \App\Models\Sistema::convertBase3($logoUrl, true);
        } catch (\Throwable $e) {
            $logoBase64 = null;
        }
    }
@endphp
<table class="table" id="topo" style="width: 100%; border-bottom: 2px black double">
    <thead>
    <tr>
        <th style=" font-weight: normal !important; ">
            @if($logoBase64)
                <img
                    src="{{ $logoBase64 }}"
                    alt="Logo" title="Logo" style="height: 45px; margin-top: 0px;">
                <br>
            @endif
            @if($dadosEmpresa)
                <h1 style="text-align: center; font-size: 15px">
                    {{ $dadosEmpresa->razao_social }}
                </h1><br>
                <p style="font-size: 8pt; text-align: center; margin-top: -10px ">
                    @if($user && $user->empresa_id != 5581)
                        CNPJ: {{ $dadosEmpresa->cnpj }}
                    @endif
                </p>
            @endif
        </th>
    </tr>
    </thead>
</table><br>
