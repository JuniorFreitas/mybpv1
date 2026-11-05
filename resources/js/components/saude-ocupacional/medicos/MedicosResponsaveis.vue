<template>
    <div id="componenteMedicosResponsaveis">
        <p class="text-center" v-if="carregando && !opcoes"><i class="fa fa-spinner fa-pulse"></i> Carregando...</p>

        <template v-if="opcoes">
            <div class="alert py-2" :class="coordenador ? 'alert-success' : 'alert-warning'">
                <template v-if="coordenador">
                    <i class="fa fa-user-md"></i> Coordenador do PCMSO hoje: <strong>{{ coordenador.nome }}</strong> ({{ coordenador.crm_completo }})
                    <span v-if="coordenador.vigencia_fim_br"> — até {{ coordenador.vigencia_fim_br }}</span>
                </template>
                <template v-else>
                    <i class="fa fa-exclamation-triangle"></i> Nenhum médico coordenador do PCMSO vigente. A NR-7 exige um médico responsável pelo PCMSO.
                </template>
            </div>

            <div class="card card-outline card-primary">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                    <h3 class="card-title mb-0">Médicos responsáveis</h3>
                    <div>
                        <button type="button" class="btn btn-sm btn-primary mr-1" @click="$refs.form.abrir(null)">
                            <i class="fa fa-plus"></i> Novo médico
                        </button>
                        <button type="button" class="btn btn-sm btn-success" :disabled="carregando" @click="carregar">
                            <i :class="carregando ? 'fa fa-sync fa-spin' : 'fa fa-sync'"></i> Atualizar
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div v-if="!medicos.length" class="text-center text-muted py-4">Nenhum médico cadastrado.</div>
                    <div v-else class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Médico</th>
                                    <th>Contato</th>
                                    <th>Vínculos</th>
                                    <th>Status</th>
                                    <th class="text-right">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="m in medicos" :key="m.id" :class="{ 'text-muted': !m.ativo }">
                                    <td>
                                        <strong>{{ m.nome }}</strong>
                                        <div class="small text-muted">{{ m.crm_completo }}<span v-if="m.rqe"> · RQE {{ m.rqe }}</span><span v-if="m.especialidade"> · {{ m.especialidade }}</span></div>
                                    </td>
                                    <td class="small">{{ m.email || '—' }}<br />{{ m.telefone || '' }}</td>
                                    <td class="small">
                                        <div v-for="v in m.vinculos" :key="v.id">
                                            <span class="badge mr-1" :class="v.vigente ? 'badge-success' : 'badge-light'">{{ v.vigente ? 'vigente' : 'fora da vigência' }}</span>
                                            {{ v.papel === 'coordenador' ? 'Coordenador do PCMSO' : 'Examinador' }}<span v-if="v.clinica"> · {{ v.clinica }}</span>
                                            <span class="text-muted"> ({{ v.vigencia_inicio_br }}{{ v.vigencia_fim_br ? ' a ' + v.vigencia_fim_br : ' em diante' }})</span>
                                        </div>
                                        <span v-if="!m.vinculos.length" class="text-muted">Sem vínculo</span>
                                    </td>
                                    <td>
                                        <span class="badge" :class="m.ativo ? 'badge-success' : 'badge-secondary'">{{ m.ativo ? 'Ativo' : 'Inativo' }}</span>
                                    </td>
                                    <td class="text-right text-nowrap">
                                        <button type="button" class="btn btn-xs btn-outline-primary mr-1" title="Editar" @click="$refs.form.abrir(m)">
                                            <i class="fa fa-pen"></i>
                                        </button>
                                        <button type="button" class="btn btn-xs mr-1" :class="m.ativo ? 'btn-outline-warning' : 'btn-outline-success'" :title="m.ativo ? 'Desativar' : 'Ativar'" @click="alternar(m)">
                                            <i :class="m.ativo ? 'fa fa-pause' : 'fa fa-play'"></i>
                                        </button>
                                        <button type="button" class="btn btn-xs btn-outline-danger" title="Excluir" @click="excluir(m)">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <medico-form-modal ref="form" :opcoes="opcoes" @salvo="carregar" />
        </template>
    </div>
</template>

<script>
import { defineComponent } from 'vue'
import axios from 'axios'
import MedicoFormModal from './MedicoFormModal.vue'

const BASE = () => `${URL_ADMIN}/saude-ocupacional/medicos`

export default defineComponent({
    name: 'MedicosResponsaveis',

    components: { MedicoFormModal },

    data() {
        return { carregando: false, medicos: [], opcoes: null, coordenador: null }
    },

    mounted() {
        this.carregar()
    },

    methods: {
        async carregar() {
            this.carregando = true
            try {
                const { data } = await axios.get(`${BASE()}/dados`)
                this.medicos = data.medicos || []
                this.opcoes = data.opcoes
                this.coordenador = data.coordenador
            } catch (e) {
                // erro exibido pelo interceptor global do axios
            } finally {
                this.carregando = false
            }
        },

        async alternar(medico) {
            try {
                const { data } = await axios.patch(`${BASE()}/${medico.id}/ativo`, { ativo: !medico.ativo })
                mostraSucesso('', data.mensagem)
                this.carregar()
            } catch (e) {
                // erro exibido pelo interceptor global do axios
            }
        },

        async excluir(medico) {
            if (!window.confirm(`Excluir ${medico.nome}? Os vínculos também saem.`)) {
                return
            }
            try {
                const { data } = await axios.delete(`${BASE()}/${medico.id}`)
                mostraSucesso('', data.mensagem)
                this.carregar()
            } catch (e) {
                // erro exibido pelo interceptor global do axios
            }
        }
    }
})
</script>
