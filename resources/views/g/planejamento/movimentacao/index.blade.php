@extends('layouts.sistema')
@section('title', 'Planejamento - Movimentação')
@section('content_header','Planejamento - Movimentação')
@section('content')

    <div class="movimentacao-page">
        <nav class="movimentacao-abas" aria-label="Tipos de movimentação">
            <button
                type="button"
                class="movimentacao-abas__arrow"
                :class="{ 'is-disabled': !abasPodeVoltar }"
                :disabled="!abasPodeVoltar"
                aria-label="Abas anteriores"
                @click="rolarAbas(-1)"
            >
                <i class="fas fa-chevron-left" aria-hidden="true"></i>
            </button>
            <div
                class="movimentacao-abas__viewport"
                ref="abasViewport"
                @scroll.passive="atualizarSetasAbas"
            >
                <div class="movimentacao-abas__track" role="tablist">
                    <button
                        v-for="aba in abasDisponiveis"
                        :key="aba.key"
                        type="button"
                        class="movimentacao-abas__item"
                        role="tab"
                        :id="aba.tabId"
                        :title="aba.title || aba.label"
                        :aria-controls="aba.panelId"
                        :aria-selected="aba_ativa === aba.key"
                        :tabindex="aba_ativa === aba.key ? 0 : -1"
                        :class="{ 'is-active': aba_ativa === aba.key }"
                        @click="trocaAba(aba.key)"
                    >
                        <i :class="aba.icon" aria-hidden="true"></i>
                        <span>@{{ aba.label }}</span>
                    </button>
                </div>
            </div>
            <button
                type="button"
                class="movimentacao-abas__arrow"
                :class="{ 'is-disabled': !abasPodeAvancar }"
                :disabled="!abasPodeAvancar"
                aria-label="Próximas abas"
                @click="rolarAbas(1)"
            >
                <i class="fas fa-chevron-right" aria-hidden="true"></i>
            </button>
        </nav>

        <div
            v-if="abas.demissao"
            id="nav-solicitacao-demissao"
            role="tabpanel"
            aria-labelledby="nav-solicitacao-demissao-tab"
        >
            <solicitacao-demissao :cliente_id="cliente_id" tela="movimentacao-demissao"></solicitacao-demissao>
        </div>
        <div
            v-if="abas.ferias"
            id="nav-ferias"
            role="tabpanel"
            aria-labelledby="nav-ferias-tab"
        >
            <solicitacao-ferias :cliente_id="cliente_id" tela="movimentacao-ferias"></solicitacao-ferias>
        </div>
        <div
            v-if="abas.admissao"
            id="nav-admissao"
            role="tabpanel"
            aria-labelledby="nav-admissao-tab"
        >
            <solicitacao-admissao :cliente_id="cliente_id" tela="movimentacao-admissao"></solicitacao-admissao>
        </div>
        <div
            v-if="abas.valorextra"
            id="nav-valorextra"
            role="tabpanel"
            aria-labelledby="nav-valorextra-tab"
        >
            <solicitacao-valor-extra :cliente_id="cliente_id" tela="movimentacao-valor-extra"></solicitacao-valor-extra>
        </div>
        <div
            v-if="abas.mudacargo"
            id="nav-mudacargo"
            role="tabpanel"
            aria-labelledby="nav-mudacargo-tab"
        >
            <solicitacao-muda-cargo :cliente_id="cliente_id" tela="movimentacao-muda-cargo"></solicitacao-muda-cargo>
        </div>
        <div
            v-if="abas.intermitente"
            id="nav-intermitente"
            role="tabpanel"
            aria-labelledby="nav-intermitente-tab"
        >
            <solicitacao-intermitente-fixo :cliente_id="cliente_id" tela="movimentacao-intermitente"></solicitacao-intermitente-fixo>
        </div>
        <div
            v-if="abas.transferencia"
            id="nav-transferencia"
            role="tabpanel"
            aria-labelledby="nav-transferencia-tab"
        >
            <solicitacao-transferencia :cliente_id="cliente_id" tela="movimentacao-transferencia"></solicitacao-transferencia>
        </div>
    </div>

@stop
@push('js')
    <script src="{{mix('js/g/planejamento/movimentacao/app.js')}}"></script>
@endpush
@push('css')
    <style>
        .movimentacao-page .movimentacao-abas {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            margin: 0.75rem 0;
            background: #f4f7f9;
            border: 1px solid #e4e8ec;
            border-radius: 8px;
            padding: 0.4rem;
        }
        .movimentacao-page .movimentacao-abas__viewport {
            flex: 1;
            min-width: 0;
            overflow-x: auto;
            overflow-y: hidden;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .movimentacao-page .movimentacao-abas__viewport::-webkit-scrollbar {
            display: none;
        }
        .movimentacao-page .movimentacao-abas__track {
            display: flex;
            flex-wrap: nowrap;
            gap: 0.35rem;
            width: max-content;
        }
        .movimentacao-page .movimentacao-abas__arrow {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            width: 32px;
            height: 32px;
            margin: 0;
            padding: 0;
            border: 1px solid #d5dde3;
            border-radius: 50%;
            background: #fff;
            color: #174257;
            cursor: pointer;
            transition: background-color 0.15s ease, color 0.15s ease, opacity 0.15s ease;
        }
        .movimentacao-page .movimentacao-abas__arrow:hover:not(:disabled) {
            background: #174257;
            border-color: #174257;
            color: #fff;
        }
        .movimentacao-page .movimentacao-abas__arrow:disabled,
        .movimentacao-page .movimentacao-abas__arrow.is-disabled {
            opacity: 0.35;
            cursor: default;
        }
        .movimentacao-page .movimentacao-abas__arrow:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(23, 66, 87, 0.25);
        }
        .movimentacao-page .movimentacao-abas__item {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            flex: 0 0 auto;
            margin: 0;
            padding: 0.45rem 0.75rem;
            border: 1px solid transparent;
            border-radius: 999px;
            background: transparent;
            color: #495057;
            font-size: 0.8125rem;
            font-weight: 600;
            line-height: 1.2;
            white-space: nowrap;
            cursor: pointer;
            transition: background-color 0.15s ease, color 0.15s ease, border-color 0.15s ease;
        }
        .movimentacao-page .movimentacao-abas__item i {
            font-size: 0.78rem;
            opacity: 0.85;
        }
        .movimentacao-page .movimentacao-abas__item:hover {
            background: rgba(23, 66, 87, 0.08);
            color: #174257;
        }
        .movimentacao-page .movimentacao-abas__item:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(23, 66, 87, 0.25);
        }
        .movimentacao-page .movimentacao-abas__item.is-active {
            background: #174257;
            border-color: #174257;
            color: #fff;
        }
        .movimentacao-page .movimentacao-abas__item.is-active i {
            opacity: 1;
        }
        .movimentacao-page .mov-tabela-wrap {
            overflow: auto;
            max-height: calc(100vh - 16rem);
        }
        .movimentacao-page table.tabela.mov-tabela {
            table-layout: auto;
            width: max-content;
            min-width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        .movimentacao-page table.tabela.mov-tabela th {
            position: sticky;
            top: 0;
            z-index: 2;
            background: #f4f6f8;
            letter-spacing: 0.02em;
            white-space: nowrap;
            font-size: 0.72rem;
        }
        .movimentacao-page table.tabela.mov-tabela td {
            vertical-align: middle;
            white-space: nowrap;
        }
        .movimentacao-page table.tabela.mov-tabela td.mov-col-texto {
            text-align: left;
            white-space: normal;
            min-width: 140px;
            max-width: 220px;
        }
    </style>
@endpush
