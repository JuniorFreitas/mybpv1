<template>
    <div class="card card-outline card-success">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h3 class="card-title mb-0">Regras de mercado (Padrão NR-7)</h3>
            <button type="button" class="btn btn-sm btn-link p-0" @click="aberto = !aberto">
                {{ aberto ? 'Ocultar' : 'Mostrar' }}
            </button>
        </div>
        <div class="card-body" v-show="aberto">
            <p class="small text-muted">Sempre ativas. Não podem ser apagadas. As regras da empresa se somam a elas.</p>
            <div class="row">
                <div class="col-12 col-md-6 col-xl-4 mb-2" v-for="(regra, i) in regras" :key="i">
                    <div class="regra-mercado">
                        <div>
                            <span class="badge mb-1" :class="regra.ativa_no_sistema ? 'badge-success' : 'badge-light'">
                                {{ regra.ativa_no_sistema ? 'Aplicada pelo sistema' : 'Orientação' }}
                            </span>
                            <div><strong>{{ regra.titulo }}</strong></div>
                        </div>
                        <p class="small mb-1 mt-1">{{ regra.texto }}</p>
                        <small class="text-muted">{{ regra.fonte }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { defineComponent } from 'vue'

export default defineComponent({
    name: 'RegrasMercado',

    props: {
        regras: { type: Array, default: () => [] }
    },

    data() {
        return { aberto: true }
    }
})
</script>

<style scoped>
.regra-mercado {
    height: 100%;
    border: 1px solid #e3e6ea;
    border-radius: 6px;
    padding: 10px 12px;
    background: #fff;
}
</style>
