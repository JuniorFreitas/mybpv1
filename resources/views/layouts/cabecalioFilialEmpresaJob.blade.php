@php
    $empresa = $dados['dados_empresa'] ?? [];
    $razaoSocial = $empresa['razao_social'] ?? '';
    $logo = $empresa['logo'] ?? null;
    $logoHeight = ($empresa['empresa_id'] ?? null) == 63122 ? '35px' : '55px';
@endphp
<table class="table" style="width: 97%; border-bottom: 2px black double; margin-bottom: 5px; margin-top: -30px">
    <thead>
        <tr>
            <th style="font-size: 15pt; font-weight: normal !important; padding-right: 10px; width: 20%">
                @if(!empty($logo))
                    <img
                        src="{{ $logo }}"
                        alt="{{ $razaoSocial }}"
                        title="{{ $razaoSocial }}"
                        style="height: {{ $logoHeight }}; margin-top: 10px;"
                    >
                    <br>
                @endif
            </th>
            <th style="color: black">
                @if($razaoSocial)
                    <h1 style="text-align: center; font-size: 14pt">
                        {{ $razaoSocial }}
                    </h1>
                @endif
                @if(!empty($empresa['cnpj']) || !empty($empresa['endereco_completo']))
                    <p style="font-size: 9pt; text-align: center; margin-top: -10px ">
                        @if(!empty($empresa['cnpj']))
                            CNPJ: {{ $empresa['cnpj'] }}
                            @if(!empty($empresa['endereco_completo']))<br>@endif
                        @endif
                        @if(!empty($empresa['endereco_completo']))
                            {{ $empresa['endereco_completo'] }}
                        @endif
                    </p>
                    <br>
                @endif
            </th>
            <th style="font-size: 17pt; font-weight: normal !important; padding-left: 10px; width: 60px">
            </th>
        </tr>
    </thead>
</table>