@if($dados['dados_empresa']['id'] != 71953)
    <table class="table" style="width: 97%; border-bottom: 2px black double; margin-bottom: 5px; margin-top: -30px">
        <thead>
        <tr>
            <th style="font-size: 15pt; font-weight: normal !important; padding-right: 10px; width: 20%">
                <img
                    src="{{ $dados['dados_empresa']['logo'] }}"
                    alt="{{$dados['dados_empresa']['razao_social']}}"
                    title="{{$dados['dados_empresa']['razao_social']}}"
                    style="height: {{$dados['dados_empresa']['empresa_id'] == 63122 ? '35px' : '55px'}}; margin-top: 10px;"
                >
                <br>
            </th>
            <th style="color: black">
                <h1 style="text-align: center; font-size: 15pt">
                    {{$dados['dados_empresa']['razao_social']}}
                </h1>
                <p style="font-size: 9pt; text-align: center; margin-top: -10px ">
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
    </table>
@else
    <table class="table" style="width: 97%; border-bottom: 2px black double; margin-bottom: 5px; margin-top: -30px">
        <thead>
        <tr>

            <th style="font-size: 17pt; font-weight: normal !important; width: 60px">
                <h1 style="text-align: center; font-size: 13pt">
                    {{$dados['dados_empresa']['razao_social']}}
                </h1>
                <p style="font-size: 9pt; text-align: center; margin-top: -10px; ">
                    CNPJ: {{$dados['dados_empresa']['cnpj']}}
                    <br>
                    {{$dados['dados_empresa']['endereco_completo']}}
                </p>
            </th>
        </tr>
        </thead>
    </table>
@endif
