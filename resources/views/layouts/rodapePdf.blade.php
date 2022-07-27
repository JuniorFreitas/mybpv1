<div style="position: absolute;bottom: -10px;font-size: 8.4pt;">
    <p style="font-size: 8.4pt; color: #444444; margin-bottom: 2.5px;">
        Esse documento foi gerado automaticamente pelo usuário {{ auth()->user()->nome }}: <br>
        Sistema Integrado MYBP em {{ (new \MasterTag\DataHora())->dataCompleta() }}
        às {{ (new \MasterTag\DataHora())->horaCompleta() }}.
    </p>
    @if(!isset($semassinatura))
        <div>
            <hr style="border:none; border-top: 1px solid #999">
            {{auth()->user()->Empresa->razao_social}}
            <br>
            @if($dados['empresa_id'] != 5581)
                CNPJ: {{auth()->user()->Empresa->cnpj}}
            @endif
            <br>
            {{auth()->user()->Empresa->endereco_completo}}
        </div>
    @endif
</div>
