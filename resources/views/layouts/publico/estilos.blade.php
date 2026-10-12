{{-- Fontes e estilos padrão das telas públicas (cartão, botões, alertas, caixas do código, carregando). --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css"
          integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz" crossorigin="anonymous">
    <style>
        /* Mesma identidade da verificação em duas etapas do login (auth/mfa.blade.php). */
        :root {
            --co-azul: #0047D6;
            --co-marinho: #0B1B3D;
            --co-noite: #070B14;
            --co-nevoa: #F4F6FA;
            --co-muted: #5b6b86;
            --co-line: rgba(11, 27, 61, 0.14);
            --co-focus: rgba(0, 71, 214, 0.18);
            --co-erro: #b02a37;
            --co-font-titulo: 'Montserrat', Arial, sans-serif;
            --co-font-texto: 'Nunito', Verdana, sans-serif;
        }

        /* Enquanto o Vue não monta, só o carregando aparece (o !important vence o display do #app). */
        [v-cloak] { display: none !important; }

        .co-carregando {
            flex: 1;
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.9rem;
            font-weight: 600;
            color: var(--co-muted);
        }

        #app[v-cloak] + .co-carregando { display: flex; }

        .co-carregando__spinner {
            width: 44px;
            height: 44px;
            border: 4px solid var(--co-line);
            border-top-color: var(--co-azul);
            border-radius: 50%;
            animation: co-girar .8s linear infinite;
        }

        @keyframes co-girar { to { transform: rotate(360deg); } }

        body.co-page {
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            font-family: var(--co-font-texto);
            color: var(--co-marinho);
            background: var(--co-nevoa);
        }

        #app { flex: 1; display: flex; flex-direction: column; }

        .co-main {
            flex: 1;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .co-card {
            width: 100%;
            max-width: 520px;
            background: #fff;
            border: 1px solid var(--co-line);
            border-radius: 1.25rem;
            box-shadow: 0 18px 44px rgba(11, 27, 61, 0.12);
            padding: 1.75rem;
        }

        /* Linha "Etapa X de 2": só aparece no tema da assinatura digital. */
        .co-card__etapa { display: none; }

        .co-card__icone {
            width: 52px;
            height: 52px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 1rem;
            background: rgba(0, 71, 214, 0.1);
            color: var(--co-azul);
            font-size: 1.25rem;
        }

        .co-card__titulo {
            margin: 0 0 0.35rem;
            font-family: var(--co-font-titulo);
            font-size: 1.3rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            line-height: 1.25;
        }

        .co-card__texto { margin: 0 0 1.25rem; font-size: 0.94rem; color: var(--co-muted); }
        .co-card__texto strong { color: var(--co-marinho); }

        .co-passos { display: flex; gap: 0.4rem; margin-bottom: 1.25rem; }
        .co-passos span { flex: 1; height: 4px; border-radius: 4px; background: var(--co-line); transition: background .2s ease; }
        .co-passos span.ativo { background: var(--co-azul); }

        .co-label { display: block; margin-bottom: 0.4rem; font-size: 0.86rem; font-weight: 700; }

        .co-input {
            width: 100%;
            min-height: 52px;
            padding: 0 1rem;
            border: 1.5px solid var(--co-line);
            border-radius: 0.8rem;
            font-family: var(--co-font-titulo);
            font-size: 1.1rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            color: var(--co-marinho);
            background: #fff;
            transition: border-color .15s ease, box-shadow .15s ease;
        }

        .co-input:focus { outline: 0; border-color: var(--co-azul); box-shadow: 0 0 0 3px var(--co-focus); }
        .co-ajuda { margin: 0.4rem 0 0; font-size: 0.82rem; color: var(--co-muted); }
        .co-ajuda a { color: inherit; font-weight: 700; text-decoration: underline; }

        .co-input.is-invalid { border-color: var(--co-erro); }
        .co-input.is-invalid:focus { box-shadow: 0 0 0 3px rgba(176, 42, 55, 0.18); }
        .co-input ~ .invalid-feedback { margin-top: 0.4rem; font-size: 0.86rem; font-weight: 600; color: var(--co-erro); }
        .co-input:disabled { background: #f1f4f6; cursor: not-allowed; }

        .co-btn {
            width: 100%;
            min-height: 48px;
            margin-top: 1rem;
            border: 0;
            border-radius: 0.8rem;
            font-family: var(--co-font-titulo);
            font-weight: 700;
            color: #fff;
            background: var(--co-marinho);
            box-shadow: 0 10px 20px rgba(11, 27, 61, 0.22);
            transition: transform .15s ease, box-shadow .2s ease, background .2s ease;
        }

        .co-btn:hover:not(:disabled) { background: #13264f; transform: translateY(-1px); }
        .co-btn:focus-visible { outline: 3px solid var(--co-focus); outline-offset: 2px; }
        .co-btn:disabled { opacity: 0.6; box-shadow: none; cursor: not-allowed; }

        .co-links { display: flex; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem; margin-top: 1rem; }

        .co-link {
            padding: 0.25rem 0;
            border: 0;
            background: none;
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--co-azul);
        }

        /* Link ao lado do botão principal ("Já recebi o código"). */
        .co-link--ao-lado { display: block; margin: 0.75rem auto 0; }

        .co-link:hover:not(:disabled) { text-decoration: underline; }
        .co-link:disabled { color: var(--co-muted); cursor: not-allowed; }

        .co-timer {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            margin: 1rem 0 0;
            font-size: 0.9rem;
            color: var(--co-muted);
            font-variant-numeric: tabular-nums;
        }

        .co-timer strong { color: var(--co-marinho); }
        .co-timer--alerta, .co-timer--alerta strong { color: var(--co-erro); }

        .co-alert { margin: 1rem 0 0; padding: 0.75rem 0.9rem; border-radius: 0.7rem; font-size: 0.9rem; }
        .co-alert--erro { background: rgba(220, 53, 69, 0.12); color: var(--co-erro); }
        .co-alert--ok { background: rgba(25, 135, 84, 0.12); color: #146c43; }
        .co-alert--info { background: rgba(0, 71, 214, 0.08); color: var(--co-marinho); }
        .co-alert--aviso { background: rgba(255, 193, 7, 0.18); color: #7a5b00; }

        .co-resumo { margin: 0 0 1.25rem; padding: 1rem; border-radius: 0.8rem; background: var(--co-nevoa); }
        .co-resumo dt { font-size: 0.74rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--co-muted); }
        .co-resumo dd { margin: 0 0 0.6rem; font-weight: 700; }
        .co-resumo dd:last-child { margin-bottom: 0; }

        .co-rodape { padding: 1.25rem 1rem; background: #fff; border-top: 1px solid var(--co-line); }
        .co-rodape__logos { display: flex; align-items: center; justify-content: center; flex-wrap: wrap; gap: 1rem; }

        /* Caixas do código (MybpCodigoInput) */
        .mybp-codigo-input { display: flex; justify-content: space-between; gap: 0.4rem; }

        .mybp-codigo-input__caixa {
            flex: 1 1 0;
            min-width: 0;
            max-width: 54px;
            height: 58px;
            padding: 0;
            border: 1.5px solid var(--co-line);
            border-radius: 0.75rem;
            background: #fff;
            text-align: center;
            text-transform: uppercase;
            font-family: var(--co-font-titulo);
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--co-marinho);
            caret-color: var(--co-azul);
            transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
        }

        .mybp-codigo-input__caixa--preenchida { border-color: var(--co-marinho); background: var(--co-nevoa); }
        .mybp-codigo-input__caixa:focus { outline: 0; border-color: var(--co-azul); box-shadow: 0 0 0 3px var(--co-focus); background: #fff; }
        .mybp-codigo-input__caixa:disabled { background: #f1f4f6; color: var(--co-muted); cursor: not-allowed; }

        .mybp-codigo-input--invalido .mybp-codigo-input__caixa { border-color: var(--co-erro); }
        .mybp-codigo-input--invalido { animation: co-tremer .35s ease; }

        @keyframes co-tremer {
            25% { transform: translateX(-6px); }
            50% { transform: translateX(6px); }
            75% { transform: translateX(-3px); }
        }

        @media (max-width: 420px) {
            .co-card { padding: 1.25rem; }
            .mybp-codigo-input { gap: 0.25rem; }
            .mybp-codigo-input__caixa { height: 48px; font-size: 1.15rem; border-radius: 0.6rem; }
        }

        @media (prefers-reduced-motion: reduce) {
            .mybp-codigo-input--invalido { animation: none; }
            .co-btn { transition: none; }
        }
    </style>
