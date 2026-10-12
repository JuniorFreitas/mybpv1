{{--
    Avisos das telas públicas: por que pedimos o CPF, privacidade (LGPD) e cookies.
    MINUTA — os textos precisam de validação do jurídico antes de produção.
    Estas telas usam somente cookies essenciais (sessão e proteção de formulário); se algum dia
    entrar analytics/marketing aqui, o aviso de cookies precisa virar pedido de consentimento.
--}}
@php
    $avisoEmpresa = trim((string) ($marca['nome'] ?? '')) ?: 'a empresa que enviou este link';
@endphp
<style>
    .mybp-avisos { margin-top: 1.5rem; font-size: .85rem; color: #5b6b74; }
    .mybp-avisos__titulo { margin: 0 0 .5rem; font-size: .78rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; }

    .mybp-avisos details {
        border: 1px solid #dbe5eb;
        border-radius: 10px;
        background: #fff;
        margin-bottom: .5rem;
    }

    .mybp-avisos summary {
        padding: .65rem .9rem;
        font-weight: 600;
        color: #1f3b48;
        cursor: pointer;
    }

    .mybp-avisos summary:focus-visible { outline: 3px solid rgba(15, 76, 96, .25); outline-offset: 2px; border-radius: 10px; }
    .mybp-avisos__corpo { padding: 0 .9rem .8rem; line-height: 1.5; }
    .mybp-avisos__corpo p { margin: 0 0 .5rem; }
    .mybp-avisos__corpo ul { margin: 0 0 .5rem; padding-left: 1.1rem; }
    .mybp-avisos__corpo p:last-child, .mybp-avisos__corpo ul:last-child { margin-bottom: 0; }

    .mybp-cookies {
        position: fixed;
        left: 1rem;
        right: 1rem;
        bottom: 1rem;
        z-index: 1040;
        max-width: 720px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: .85rem 1rem;
        border: 1px solid #cfe0e8;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 10px 28px rgba(8, 41, 54, .16);
        font-size: .85rem;
        color: #1f2d35;
    }

    .mybp-cookies[hidden] { display: none; }
    .mybp-cookies p { margin: 0; flex: 1; }
    .mybp-cookies a { color: #0f4c60; font-weight: 600; text-decoration: underline; }

    .mybp-cookies button {
        flex-shrink: 0;
        min-height: 36px;
        padding: .35rem .9rem;
        border: 0;
        border-radius: .3rem;
        background: #0f4c60;
        color: #fff;
    }

    @media (max-width: 480px) {
        .mybp-cookies { flex-direction: column; align-items: stretch; }
    }
</style>

<section class="mybp-avisos" id="avisos-publicos" aria-label="Avisos de privacidade">
    <p class="mybp-avisos__titulo">Privacidade e segurança</p>

    <details id="aviso-cpf">
        <summary>Por que pedimos o seu CPF?</summary>
        <div class="mybp-avisos__corpo">
            <p>
                O CPF serve para confirmar que quem abriu este link é a pessoa a quem ele foi enviado.
                Só depois dessa conferência enviamos o código de acesso para o e-mail do seu cadastro.
            </p>
            <ul>
                <li>O CPF digitado é apenas comparado com o que já consta no seu cadastro em {{ $avisoEmpresa }}.</li>
                <li>Ele não é usado para consulta de crédito, cadastro em outros serviços nem publicidade.</li>
                <li>Por segurança, registramos data, hora, endereço IP e navegador de cada tentativa de acesso.</li>
            </ul>
        </div>
    </details>

    <details id="aviso-privacidade">
        <summary>Aviso de privacidade</summary>
        <div class="mybp-avisos__corpo">
            <p>
                <strong>Quem trata seus dados:</strong> {{ $avisoEmpresa }} é a controladora. O MyBPIN é a
                plataforma (operadora) que ela usa para este processo.
            </p>
            <p>
                <strong>Quais dados:</strong> CPF, nome, e-mail, os documentos e informações que você enviar,
                além de registros técnicos de acesso (data, hora, IP e navegador).
            </p>
            <p>
                <strong>Para quê:</strong> identificar você, conduzir o processo de admissão ou a assinatura
                do documento e comprovar que o ato foi feito por você.
            </p>
            <p>
                <strong>Base legal:</strong> procedimentos preliminares a contrato a seu pedido, cumprimento
                de obrigação legal e exercício regular de direitos (Lei nº 13.709/2018 — LGPD, art. 7º, II, V e VI).
            </p>
            <p>
                <strong>Por quanto tempo:</strong> pelo prazo necessário ao processo e pelos prazos de guarda
                exigidos em lei; depois disso os dados são eliminados.
            </p>
            <p>
                <strong>Seus direitos:</strong> você pode pedir confirmação, acesso, correção e eliminação dos
                seus dados (LGPD, art. 18) pelos canais de atendimento do RH de {{ $avisoEmpresa }}.
            </p>
        </div>
    </details>

    <details id="aviso-cookies">
        <summary>Cookies</summary>
        <div class="mybp-avisos__corpo">
            <p>
                Esta página usa <strong>somente cookies essenciais</strong>, sem os quais o acesso não funciona:
            </p>
            <ul>
                <li><strong>Sessão:</strong> mantém você identificado depois de informar o CPF e o código.</li>
                <li><strong>Segurança:</strong> protege o envio dos formulários contra fraude (CSRF).</li>
            </ul>
            <p>
                Não usamos cookies de publicidade nem de rastreamento aqui. Por serem essenciais, esses cookies
                não dependem de consentimento; eles expiram quando a sessão termina.
            </p>
        </div>
    </details>
</section>

<div class="mybp-cookies" id="mybp-cookies" role="region" aria-label="Aviso de cookies" hidden>
    <p>
        Usamos apenas cookies essenciais para manter seu acesso seguro.
        <a href="#aviso-cookies" data-abre-aviso="aviso-cookies">Saiba mais</a>
    </p>
    <button type="button" id="mybp-cookies-ok">Entendi</button>
</div>

<script>
(function () {
    var CHAVE = 'mybp_aviso_cookies_ok';
    var faixa = document.getElementById('mybp-cookies');
    var visto = false;
    try { visto = window.localStorage.getItem(CHAVE) === '1'; } catch (e) {}
    if (faixa && !visto) faixa.hidden = false;

    var botao = document.getElementById('mybp-cookies-ok');
    if (botao) {
        botao.addEventListener('click', function () {
            try { window.localStorage.setItem(CHAVE, '1'); } catch (e) {}
            faixa.hidden = true;
        });
    }

    // Links "Saiba mais" (inclusive os renderizados depois pelo Vue) abrem o aviso correspondente.
    document.addEventListener('click', function (evento) {
        var link = evento.target.closest ? evento.target.closest('[data-abre-aviso]') : null;
        if (!link) return;
        var aviso = document.getElementById(link.getAttribute('data-abre-aviso'));
        if (!aviso) return;
        evento.preventDefault();
        aviso.open = true;
        aviso.scrollIntoView({ behavior: 'smooth', block: 'start' });
        var resumo = aviso.querySelector('summary');
        if (resumo) resumo.focus({ preventScroll: true });
    });
})();
</script>
