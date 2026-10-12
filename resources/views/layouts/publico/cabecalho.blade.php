{{--
    Cabeçalho padrão das telas públicas (sem login): logo do cliente (esquerda) + logo MyBPIN (direita).
    Uso: @include('layouts.publico.cabecalho', ['marca' => app(MarcaClienteService::class)->dados($empresa)])
    - $marca['logo_url']: logotipo do cadastro do cliente (Administração > Clientes).
    - Sem logotipo (ou se a imagem falhar), mostra o nome do cliente no lugar.
    Deve ficar fora do #app do Vue (é estático e aparece já durante o carregamento).
--}}
@php
    $marcaNome = trim((string) ($marca['nome'] ?? ''));
    $marcaLogo = $marca['logo_url'] ?? null;
@endphp
<style>
    .mybp-pub-topo { background: #fff; border-bottom: 1px solid rgba(11, 27, 61, 0.14); }

    .mybp-pub-topo__conteudo {
        max-width: 960px;
        margin: 0 auto;
        padding: 0.75rem 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }

    .mybp-pub-topo__mybpin { display: block; height: 24px; width: auto; margin-left: auto; opacity: 0.85; }

    .mybp-pub-topo__cliente {
        min-width: 0;
        max-width: 70%;
        display: flex;
        align-items: center;
    }

    /* Mesmo critério do site de vagas (site-header__logo): proporção mantida, um pouco menor — 60px de altura, 88px quando a logo é vertical. */
    .mybp-pub-topo__cliente img {
        display: block;
        width: auto;
        height: auto;
        max-width: 100%;
        max-height: 60px;
        object-fit: contain;
        object-position: left center;
    }

    .mybp-pub-topo__cliente img.is-portrait { max-height: 88px; }

    .mybp-pub-topo__nome {
        font-family: 'Montserrat', Arial, sans-serif;
        font-weight: 700;
        font-size: 0.95rem;
        line-height: 1.2;
        color: #0B1B3D;
    }

    .mybp-pub-topo__nome[hidden] { display: none; }

    @media (max-width: 420px) {
        .mybp-pub-topo__mybpin { height: 20px; }
        .mybp-pub-topo__nome { font-size: 0.82rem; }
    }
</style>
<header class="mybp-pub-topo">
    <div class="mybp-pub-topo__conteudo">
        @if($marcaLogo || $marcaNome !== '')
            <div class="mybp-pub-topo__cliente">
                @if($marcaLogo)
                    <img src="{{ $marcaLogo }}" alt="{{ $marcaNome }}"
                         onload="this.classList.toggle('is-portrait', this.naturalHeight > this.naturalWidth)"
                         onerror="this.hidden = true; this.nextElementSibling.hidden = false;">
                @endif
                <span class="mybp-pub-topo__nome" @if($marcaLogo) hidden @endif>{{ $marcaNome }}</span>
            </div>
        @endif
        <img class="mybp-pub-topo__mybpin" src="{{ asset('images/mybpin-logo-fundo-claro.webp') }}" alt="MyBPIN" width="91" height="24">
    </div>
</header>
