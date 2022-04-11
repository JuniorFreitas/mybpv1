<div style="position: absolute;bottom: -10px;font-size: 8.4pt;">
    <p style="font-size: 8.4pt; color: #444444; margin-bottom: 2.5px;">
        Esse documento foi gerado automaticamente pelo usuário {{ auth()->user()->nome }}: <br>
        Sistema Integrado MYBP em {{ (new \MasterTag\DataHora())->dataCompleta() }}
        às {{ (new \MasterTag\DataHora())->horaCompleta() }}.
    </p>
    <div>
        <hr style="border:none; border-top: 1px solid #999">
        {{auth()->user()->Empresa->Cliente->razao_social}}
        <br>
        CNPJ: {{auth()->user()->Empresa->Cliente->cnpj}}
        <br>
        {{auth()->user()->Empresa->Cliente->endereco_completo}}
    </div>
</div>
