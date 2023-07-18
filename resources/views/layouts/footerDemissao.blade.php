<div class="footer">
    <p class="obs">
        Esse documento foi gerado automaticamente pelo usuário {{ auth()->user()->nome }}: <br>
        Sistema Integrado BPIN by MyBP em {{ (new \MasterTag\DataHora())->dataCompleta() }}
        às {{ (new \MasterTag\DataHora())->horaCompleta() }}.
    </p>
    <div>
        <hr style="border:none; border-top: 1px solid #999">
        {{$dados->Feedback->Empresa->razao_social}}
        <br>
        CNPJ: {{$dados->Feedback->Empresa->cnpj}} <br>
        {{$dados->Feedback->endereco_completo}}
    </div>
</div>
