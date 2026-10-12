{{--
    Deixa os componentes públicos (AcessoPorCodigo, caixas do código, carregando) com o visual das
    telas de assinatura digital. Usar em views que estendem assinatura.layout, depois de
    layouts.publico.estilos.
--}}
<style>
    .assin-page #app { display: block; }

    .assin-page .co-card {
        max-width: none;
        padding: 0;
        border: 1px solid var(--assin-card-border);
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 10px 28px rgba(8, 41, 54, .08);
        font-family: inherit;
        color: #1f2d35;
    }

    .assin-page .co-passos,
    .assin-page .co-card__icone { display: none; }

    .assin-page .co-card__cabecalho {
        background: linear-gradient(90deg, var(--assin-primary) 0%, #166179 100%);
        color: #fff;
        padding: 1rem 1.25rem;
    }

    .assin-page .co-card__titulo {
        margin: 0;
        font-family: inherit;
        font-size: 1.25rem;
        font-weight: 500;
        letter-spacing: 0;
        color: #fff;
    }

    .assin-page .co-card__etapa { display: block; margin: .15rem 0 0; font-size: .9rem; opacity: .9; }
    .assin-page .co-card__corpo { padding: 1.25rem; }
    .assin-page .co-card__texto { margin-bottom: .9rem; color: var(--assin-muted); }

    .assin-page .co-form {
        border: 1px solid #dbe5eb;
        border-radius: 12px;
        background: #f8fbfd;
        padding: 1rem;
    }

    .assin-page .co-label { font-size: .9rem; font-weight: 600; color: #1f2d35; }

    .assin-page .co-input {
        min-height: 40px;
        padding: .375rem .75rem;
        border: 1px solid #ced4da;
        border-radius: .3rem;
        font-family: inherit;
        font-size: 1rem;
        font-weight: 400;
        letter-spacing: 0;
        color: #1f2d35;
    }

    .assin-page .co-input:focus,
    .assin-page .mybp-codigo-input__caixa:focus {
        border-color: var(--assin-primary);
        box-shadow: 0 0 0 3px rgba(15, 76, 96, .18);
    }

    .assin-page .co-btn {
        width: auto;
        min-height: 38px;
        padding: .375rem .9rem;
        border-radius: .3rem;
        font-family: inherit;
        font-weight: 400;
        background: var(--assin-primary);
        box-shadow: none;
    }

    .assin-page .co-btn:hover:not(:disabled) { background: var(--assin-primary-dark); transform: none; }

    .assin-page .co-link--ao-lado { display: inline-block; margin: 1rem 0 0 .9rem; }
    .assin-page .co-links { justify-content: flex-start; gap: 1.25rem; }
    .assin-page .co-link { font-weight: 400; color: var(--assin-primary); }
    .assin-page .co-timer { justify-content: flex-start; }
    .assin-page .co-timer strong { color: #1f2d35; }
    .assin-page .co-alert { border-radius: 10px; }

    .assin-page .mybp-codigo-input { justify-content: flex-start; gap: .5rem; }

    .assin-page .mybp-codigo-input__caixa {
        font-family: inherit;
        color: #1f2d35;
        caret-color: var(--assin-primary);
        border-radius: .5rem;
    }

    .assin-page .mybp-codigo-input__caixa--preenchida { border-color: var(--assin-primary); background: #f1f6f8; }

    .assin-page .co-carregando { min-height: 40vh; }
    .assin-page .co-carregando__spinner { border-top-color: var(--assin-primary); }
</style>
