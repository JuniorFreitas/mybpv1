<table class="table" style="width: 100%; border-bottom: 2px black double">
    <thead>
    <tr>
        <th style="font-size: 17pt; font-weight: normal !important; padding-right: 10px; width: 60px">
            @if(!is_null($usuario['logo']))
                <img
                    src="{{ $usuario['logo'] }}"
                    alt="BPSE" title="BPSE" style="height: 70px; margin-top: 0px;">
                <br>
            @endif
        </th>
        <th style="color: black">
            <h1 style="text-align: center; font-size: 19px">
                {{ $usuario['razao_social'] }}
            </h1><br>
            <p style="font-size: 8pt; text-align: center; margin-top: -10px ">
                @if($usuario['empresa_id'] != 5581)
                    CNPJ: {{ $usuario['cnpj'] }}
                @endif
            </p>
            <br>
        </th>
        <th style="font-size: 17pt; font-weight: normal !important; padding-left: 10px; width: 60px">
        </th>
    </tr>
    </thead>
</table><br>
