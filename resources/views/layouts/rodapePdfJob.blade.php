<div style="position: absolute;bottom: -10px;font-size: 8.4pt;">
    <p style="font-size: 8.4pt; color: #444444; margin-bottom: 2.5px;">
        Esse documento foi gerado automaticamente pelo usuário {{ $usuario['nome'] }}: <br>
        Sistema Integrado MYBP em {{ (new \MasterTag\DataHora())->dataCompleta() }}
        às {{ (new \MasterTag\DataHora())->horaCompleta() }}.
    </p>
    @if(!isset($semassinatura))
        <div>
            <hr style="border:none; border-top: 1px solid #999">
            {{ $usuario['razao_social'] }}
            <br>
            @if($usuario['empresa_id'] != 5581)
                CNPJ: {{ $usuario['cnpj'] }}
            @endif
            <br>
            {{ $usuario['endereco'] }}
        </div>
    @endif
</div>
