<template>
    <div class="carteiras-etiquetas-templates mt-3 pt-2">
        <ul class="nav nav-tabs bg-light mb-4" role="tablist" style="border-bottom: 1px solid #653232">
            <li class="nav-item">
                <a
                    class="nav-link"
                    :class="{ active: abaAtiva === 'carteiras' }"
                    href="#aba-carteiras"
                    role="tab"
                    :aria-selected="abaAtiva === 'carteiras'"
                    @click.prevent="selecionarAba('carteiras')"
                >
                    <i class="fas fa-id-card mr-1"></i> Carteiras
                </a>
            </li>
            <li class="nav-item">
                <a
                    class="nav-link"
                    :class="{ active: abaAtiva === 'etiquetas' }"
                    href="#aba-etiquetas"
                    role="tab"
                    :aria-selected="abaAtiva === 'etiquetas'"
                    @click.prevent="selecionarAba('etiquetas')"
                >
                    <i class="fas fa-ban mr-1"></i> Etiquetas de Bloqueio
                </a>
            </li>
        </ul>

        <p class="text-muted small mb-3" v-if="abaAtiva === 'carteiras'">
            Modelos visuais do PDF da carteira.
            <template v-if="isAdminGlobal">
                Cadastre templates <strong>globais</strong> (todas as empresas) ou <strong>customizados</strong> para uma empresa específica.
            </template>
            <template v-else>
                Cadastre templates <strong>customizados</strong> da sua empresa (globais entram só no combo do padrão).
            </template>
        </p>
        <p class="text-muted small mb-3" v-else>
            Modelos de textos/ramal do PDF de etiqueta de bloqueio.
            <template v-if="isAdminGlobal">
                Cadastre templates <strong>globais</strong> ou <strong>customizados</strong> para uma empresa específica.
            </template>
            <template v-else>
                Cadastre templates <strong>customizados</strong> da sua empresa.
            </template>
        </p>

        <carteiras-treinamento-cadastro
            v-if="abaAtiva === 'carteiras'"
            :is-admin-global="isAdminGlobal"
        />
        <etiquetas-bloqueio-cadastro
            v-if="abaAtiva === 'etiquetas'"
            :is-admin-global="isAdminGlobal"
        />
    </div>
</template>

<script>
import CarteirasTreinamentoCadastro from '../carteirastreinamento/CarteirasTreinamentoCadastro'
import EtiquetasBloqueioCadastro from '../etiquetasbloqueio/EtiquetasBloqueioCadastro'

const ABAS = ['carteiras', 'etiquetas']

export default {
    components: {
        CarteirasTreinamentoCadastro,
        EtiquetasBloqueioCadastro,
    },
    props: {
        isAdminGlobal: { type: Boolean, default: false },
        abaInicial: { type: String, default: 'carteiras' },
    },
    data() {
        return {
            abaAtiva: this.normalizarAba(this.abaInicial),
        }
    },
    mounted() {
        const params = new URLSearchParams(window.location.search)
        const abaUrl = params.get('aba')
        if (abaUrl) {
            this.abaAtiva = this.normalizarAba(abaUrl)
        }
        this.sincronizarAbaNaUrl()
    },
    methods: {
        normalizarAba(aba) {
            return ABAS.includes(aba) ? aba : 'carteiras'
        },
        selecionarAba(aba) {
            this.abaAtiva = this.normalizarAba(aba)
            this.sincronizarAbaNaUrl()
        },
        sincronizarAbaNaUrl() {
            const url = new URL(window.location.href)
            if (this.abaAtiva === 'carteiras') {
                url.searchParams.delete('aba')
            } else {
                url.searchParams.set('aba', this.abaAtiva)
            }
            window.history.replaceState({}, '', url.toString())
        },
    },
}
</script>
