<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Carta Oferta</title>
    <link rel="preload" href="{{mix('js/app.js')}}" as="script">
    <link rel="preload" href="{{mix('js/funcoes.js')}}" as="script">
    @include('layouts.favicon')
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
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
</head>
<body class="co-page">
@include('layouts.publico.cabecalho', ['marca' => $marca])
<div id="app" v-cloak>
    <preload v-if="preload" :msg="msgPreload"></preload>
    <input type="hidden" id="dd" value="{{ json_encode($payload) }}">

    <main class="co-main">
        {{-- Gate CPF + código --}}
        <section v-if="!dados.autenticado" class="co-card">
            <div class="co-passos" aria-hidden="true">
                <span class="ativo"></span>
                <span :class="{ ativo: passo === 'codigo' }"></span>
            </div>

            <form v-if="passo === 'cpf'" @submit.prevent="solicitarCodigo(false)" novalidate>
                <div class="co-card__icone"><i class="fa fa-lock"></i></div>
                <h1 class="co-card__titulo">Acesso à Carta Oferta</h1>
                <p class="co-card__texto">
                    Por segurança, informe seu CPF. Enviaremos um código de @{{ tamanhoCodigo }} caracteres
                    para o e-mail cadastrado.
                </p>

                <label class="co-label" for="co-cpf">CPF</label>
                <input
                    id="co-cpf"
                    ref="cpfInput"
                    type="text"
                    class="co-input"
                    v-model="cpf"
                    v-mascara:cpf
                    maxlength="14"
                    inputmode="numeric"
                    autocomplete="off"
                    placeholder="000.000.000-00"
                    :disabled="enviando"
                    @blur="validarCpf(false)"
                >
                <button type="submit" class="co-btn" :disabled="enviando">
                    <i class="fa" :class="enviando ? 'fa-spinner fa-spin' : 'fa-paper-plane'"></i>
                    Enviar código
                </button>
            </form>

            <form v-else @submit.prevent="validarCodigo" novalidate>
                <div class="co-card__icone"><i class="fa fa-envelope-open"></i></div>
                <h1 class="co-card__titulo">Digite o código</h1>
                <p class="co-card__texto">
                    Enviamos um código de @{{ tamanhoCodigo }} caracteres
                    <template v-if="emailMascarado">para <strong>@{{ emailMascarado }}</strong></template
                    ><template v-else>para o e-mail cadastrado</template>. Ele só pode ser usado uma vez.
                </p>

                <mybp-codigo-input
                    ref="codigoInput"
                    v-model="codigo"
                    :tamanho="tamanhoCodigo"
                    :disabled="!podeDigitarCodigo"
                    :invalido="codigoInvalido"
                    rotulo="Código de acesso"
                    @completo="validarCodigo"
                ></mybp-codigo-input>

                <p v-if="bloqueado" class="co-timer co-timer--alerta" role="status">
                    <i class="fa fa-ban"></i>
                    Bloqueado por tentativas. Tente novamente em <strong>@{{ formatarTempo(bloqueioSegundos) }}</strong>
                </p>
                <p v-else-if="codigoExpirado" class="co-timer co-timer--alerta" role="status">
                    <i class="fa fa-clock"></i>
                    O código expirou. Reenvie para receber um novo.
                </p>
                <p v-else class="co-timer" :class="{ 'co-timer--alerta': codigoSegundos <= 60 }">
                    <i class="fa fa-clock"></i>
                    Código válido por <strong>@{{ formatarTempo(codigoSegundos) }}</strong>
                </p>

                <button type="submit" class="co-btn" :disabled="!podeDigitarCodigo || codigo.length < tamanhoCodigo">
                    <i class="fa" :class="enviando ? 'fa-spinner fa-spin' : 'fa-check'"></i>
                    Validar e continuar
                </button>

                <div class="co-links">
                    <button type="button" class="co-link" :disabled="!podeReenviar" @click="solicitarCodigo(true)">
                        Reenviar código<template v-if="reenvioSegundos > 0 && !bloqueado"> (@{{ reenvioSegundos }}s)</template>
                    </button>
                    <button type="button" class="co-link" :disabled="enviando" @click="alterarCpf">
                        Alterar CPF
                    </button>
                </div>
            </form>

            <div v-if="aviso" class="co-alert co-alert--ok" role="status">@{{ aviso }}</div>
            <div v-if="erro" class="co-alert co-alert--erro" role="alert">@{{ erro }}</div>
        </section>

        {{-- Conteúdo autenticado (dados mínimos) --}}
        <section v-else class="co-card">
            <h1 class="co-card__titulo">Carta Oferta</h1>
            <p class="co-card__texto">@{{ dados.empresa?.nome }}</p>

            <dl class="co-resumo">
                <dt>Nome</dt>
                <dd>@{{ dados.candidato?.nome }}</dd>
                <dt>Cargo</dt>
                <dd>@{{ dados.cargo }}</dd>
            </dl>

            <div v-if="dados.status === 'Aceito pelo RH'" class="co-alert co-alert--ok mt-0">
                <i class="fa fa-check-circle"></i> Carta Oferta aceita com sucesso!
            </div>
            <div v-else-if="dados.status === 'Aguardando RH'" class="co-alert co-alert--aviso mt-0">
                <i class="fa fa-hourglass-half"></i>
                Agradecemos o envio! Carta Oferta aguardando verificação pelo RH.
                Fique atento(a) ao seu e-mail.
            </div>
            <div v-else-if="dados.status === 'Recusado pelo RH'" class="co-alert co-alert--erro mt-0">
                <i class="fa fa-times-circle"></i> Carta Oferta recusada!
            </div>
            <div v-else-if="dados.status === 'Expirado'" class="co-alert co-alert--erro mt-0">
                <i class="fa fa-clock"></i> Prazo expirado!
            </div>
            <template v-else-if="dados.status === 'Pendente Anexo'">
                <div class="co-alert co-alert--info mt-0 mb-3">
                    Anexe a carta oferta assinada em PDF para continuar.
                </div>
                <fieldset>
                    <legend>Anexar CARTA OFERTA</legend>
                    <upload
                        label="Anexar"
                        :dados-ajax="{ tipo: 'carta_oferta', curriculo_id: dados.curriculo_id }"
                        :model="anexos"
                        :model-delete="anexosDel"
                        :url="urlAnexoUpload"
                        :apenas-pdf="true"
                        @onprogresso="anexoUploadAndamento = true"
                        @onfinalizado="anexoUploadAndamento = false"
                        :quantidade="1"
                        :multi="false"
                    ></upload>
                </fieldset>
                <button
                    type="button"
                    class="co-btn"
                    v-if="anexos.length && !anexos[0].falhou && !anexoUploadAndamento"
                    @click.prevent="salvar"
                >Salvar e enviar</button>
            </template>

            <div v-if="erro" class="co-alert co-alert--erro" role="alert">@{{ erro }}</div>
        </section>
    </main>

    <footer class="co-rodape">
        <div class="co-rodape__logos">
            <img src="{{ asset('images/inova_maranhao.png') }}" alt="" style="height: 60px">
            <img src="{{ asset('images/fapema-logo.png') }}" alt="" style="height: 60px">
            <img src="https://bpse.com.br/img/logo_procem.png" alt="" style="height: 90px">
            <img src="https://bpse.com.br/img/selo_gptw.png" alt="" style="height: 90px">
        </div>
    </footer>
</div>
<div class="co-carregando" role="status" aria-live="polite">
    <div class="co-carregando__spinner"></div>
    Carregando...
</div>
<script src="{{mix('js/app.js')}}"></script>
<script src="{{mix('js/funcoes.js')}}"></script>
<script src="{{mix('js/cartaoferta/app.js')}}"></script>
</body>
</html>
