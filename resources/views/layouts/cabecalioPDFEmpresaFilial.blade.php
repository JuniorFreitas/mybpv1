<table class="table" id="topo" style="width: 100%; border-bottom: 2px black double">
    <thead>
    <tr>
        <th style="text-align: center; font-weight: normal !important; padding-right: 10px; width: 60px">
            <img src="{{ $dados['dados_empresa']['logo'] }}"
            alt="{{$dados['dados_empresa']['razao_social']}}" title="{{$dados['dados_empresa']['razao_social']}}" style="height: 80px; margin-top: 10px;">
        </th>
        <th style="color: black">
            <h1 style="text-align: center; font-size: 19px">
                {{$dados['dados_empresa']['razao_social']}}
            </h1><br>
            <p style="font-size: 8pt; text-align: center; margin-top: -10px ">
                CNPJ: {{$dados['dados_empresa']['cnpj']}}
                <br>
                {{$dados['dados_empresa']['endereco_completo']}}
            </p>
            <br>
        </th>
        <th style="font-size: 17pt; font-weight: normal !important; padding-left: 10px; width: 60px">
        </th>
    </tr>
    </thead>
</table><br>
