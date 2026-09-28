<template>
    <div class="exames-admin-hub">
        <ul class="nav nav-tabs mb-3">
            <li class="nav-item" v-for="aba in abasVisiveis" :key="aba.id">
                <a
                    href="#"
                    class="nav-link"
                    :class="{ active: abaAtiva === aba.id }"
                    @click.prevent="abaAtiva = aba.id"
                >
                    {{ aba.label }}
                </a>
            </li>
        </ul>

        <empresa-exame v-if="abaAtiva === 'clinicas'"></empresa-exame>
        <exame-tipos v-else-if="abaAtiva === 'tipos'"></exame-tipos>
        <exame-formulario-builder v-else-if="abaAtiva === 'formularios'" contexto="encaminhamento"></exame-formulario-builder>
        <exame-formulario-builder v-else-if="abaAtiva === 'sesmt'" contexto="resultado"></exame-formulario-builder>
        <exame-catalogo v-else-if="abaAtiva === 'catalogo'"></exame-catalogo>
    </div>
</template>

<script>
import { defineComponent } from 'vue'
import EmpresaExame from '../empresaexame/EmpresaExame.vue'
import ExameTipos from './ExameTipos.vue'
import ExameCatalogo from './ExameCatalogo.vue'
import ExameFormularioBuilder from './ExameFormularioBuilder.vue'

export default defineComponent({
    name: 'ExamesAdminHub',
    components: {
        EmpresaExame,
        ExameTipos,
        ExameCatalogo,
        ExameFormularioBuilder
    },
    data() {
        return {
            abaAtiva: 'clinicas',
            abas: [
                { id: 'clinicas', label: 'Clínicas', perm: true },
                { id: 'tipos', label: 'Tipos de exame', perm: true },
                { id: 'formularios', label: 'Formulários', perm: true },
                { id: 'sesmt', label: 'Resultado SESMT', perm: true },
                { id: 'catalogo', label: 'Lista de exames', perm: true }
            ]
        }
    },
    computed: {
        abasVisiveis() {
            return this.abas
        }
    },
    mounted() {
        const hash = (window.location.hash || '').replace('#', '')
        if (this.abas.some((a) => a.id === hash)) {
            this.abaAtiva = hash
        }
    },
    watch: {
        abaAtiva(val, oldVal) {
            // Evita backdrop Bootstrap preso ao trocar aba (modais destruídos via v-if)
            if (window.$) {
                window.$('.modal').modal('hide')
            }
            document.body.classList.remove('modal-open')
            document.querySelectorAll('.modal-backdrop').forEach((el) => el.remove())
            if (window.history && window.history.replaceState) {
                window.history.replaceState(null, '', `#${val}`)
            }
        }
    }
})
</script>
