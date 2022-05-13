<table class="table" style="width: 100%; border-bottom: 2px black double">
    <thead>
    <tr>
        <th style="font-size: 17pt; font-weight: normal !important; padding-right: 10px; width: 60px">
            @if(count(auth()->user()->ClientesLogo) > 0)
                <img
                    src="{{auth()->user()->ClientesLogo[0]->urlThumb}}"
                    alt="BPSE" title="BPSE" style="height: 70px; margin-top: 0px;">
                <br>
            @endif
        </th>
        <th style="color: black">
            <h1 style="text-align: center; font-size: 19px">
                {{auth()->user()->DadosEmpresa->razao_social}}
            </h1><br>
            <p style="font-size: 8pt; text-align: center; margin-top: -10px ">
                CNPJ: {{auth()->user()->DadosEmpresa->cnpj}}
            </p>
            <br>
        </th>
        <th style="font-size: 17pt; font-weight: normal !important; padding-left: 10px; width: 60px">
        </th>
    </tr>
    </thead>
</table><br>
