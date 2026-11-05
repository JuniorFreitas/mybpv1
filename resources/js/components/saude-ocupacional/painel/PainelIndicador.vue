<template>
    <div class="painel-indicador" :class="`painel-indicador--${cor}`">
        <div class="painel-indicador__topo">
            <i :class="icone" aria-hidden="true"></i>
            <span class="painel-indicador__titulo">{{ titulo }}</span>
        </div>
        <div class="painel-indicador__valor">{{ valor }}</div>
        <div class="painel-indicador__descricao">{{ descricao }}</div>
        <a v-if="link && valor > 0" :href="link" class="painel-indicador__acao">
            {{ textoAcao }} <i class="fa fa-arrow-right" aria-hidden="true"></i>
        </a>
        <button v-else-if="emiteAcao && valor > 0" type="button" class="painel-indicador__acao btn btn-link p-0" @click="$emit('acao')">
            {{ textoAcao }} <i class="fa fa-arrow-down" aria-hidden="true"></i>
        </button>
    </div>
</template>

<script>
import { defineComponent } from 'vue'

export default defineComponent({
    name: 'PainelIndicador',

    props: {
        titulo: { type: String, required: true },
        valor: { type: Number, default: 0 },
        descricao: { type: String, default: '' },
        icone: { type: String, default: 'fa fa-circle' },
        // perigo | alerta | info | sucesso | neutro
        cor: { type: String, default: 'neutro' },
        link: { type: String, default: null },
        textoAcao: { type: String, default: 'Ver lista' },
        emiteAcao: { type: Boolean, default: false }
    },

    emits: ['acao']
})
</script>

<style scoped>
.painel-indicador {
    height: 100%;
    border: 1px solid #e3e6ea;
    border-left-width: 4px;
    border-radius: 6px;
    padding: 12px 14px;
    background: #fff;
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.painel-indicador--perigo { border-left-color: #dc3545; }
.painel-indicador--alerta { border-left-color: #f0ad4e; }
.painel-indicador--info { border-left-color: #17a2b8; }
.painel-indicador--sucesso { border-left-color: #28a745; }
.painel-indicador--neutro { border-left-color: #6c757d; }

.painel-indicador__topo {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #6c757d;
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
}
.painel-indicador__valor {
    font-size: 2rem;
    font-weight: 700;
    line-height: 1.1;
    color: #212529;
}
.painel-indicador__descricao {
    font-size: 0.85rem;
    color: #6c757d;
    flex: 1;
}
.painel-indicador__acao {
    font-size: 0.85rem;
    font-weight: 600;
    margin-top: 6px;
    text-align: left;
}
</style>
