<div id="rodape" style="position: absolute;bottom: 16px;font-size: 6pt;">
    <p style="font-size: 6pt; color: #444444; margin-bottom: 2.5px;">
        Esse documento foi gerado automaticamente por {{ auth()->user()->nome }}:
        Sistema Integrado BPIN by MyBP em {{ (new \MasterTag\DataHora())->dataCompleta() }}
        às {{ (new \MasterTag\DataHora())->horaCompleta() }}.
    </p>
    @if(!isset($semassinatura))
        <div>
            <hr style="border:none; border-top: 1px solid #999">
            {{auth()->user()->Empresa->razao_social}} -
            @if(auth()->user()->empresa_id != 5581)
                CNPJ: {{auth()->user()->Empresa->cnpj}}
            @endif
            <br>
            {{auth()->user()->Empresa->endereco_completo}}
        </div>
    @endif
</div>
