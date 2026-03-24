<div class="rodape-pdf-filial-job">
    <p class="rodapeAssinatura">
        Esse documento foi gerado automaticamente por {{ $dados['solicitante'] }}: <br>
        Sistema Integrado BPIN by MyBP em {{ (new \MasterTag\DataHora())->dataCompleta() }}
        &agrave;s {{ (new \MasterTag\DataHora())->horaCompleta() }}.
    </p>
</div>

<style type="text/css">
    .rodape-pdf-filial-job {
        font-size: 8.4pt;
        line-height: 1.25;
        min-height: 24px;
    }

    .rodapeAssinatura {
        font-size: 8.4pt;
        margin: 0;
        text-align: left;
        color: #888888 !important;
    }
</style>
