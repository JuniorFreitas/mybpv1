<template>
    <modal id="modalRiscoCatalogo" ref="modal" titulo="Catálogo de riscos" :size="60" :mostrar-botao-fechar-no-rodape="false">
        <template #conteudo>
            <div class="mybp-modal-form mybp-filtros-compactos">
                <fieldset class="mybp-modal-secao">
                    <legend>{{ form.id ? 'Editar risco' : 'Novo risco' }}</legend>
                    <div class="form-row align-items-end">
                        <div class="col-12 col-md-6 form-group">
                            <label class="mybp-label" for="risco-label">Nome</label>
                            <input id="risco-label" type="text" maxlength="120" class="form-control form-control-sm" v-model.trim="form.label" placeholder="Ex.: Ruído contínuo" />
                        </div>
                        <div class="col-8 col-md-4 form-group">
                            <label class="mybp-label" for="risco-tipo">Tipo</label>
                            <select id="risco-tipo" class="form-control form-control-sm" v-model.number="form.risco_tipo">
                                <option :value="0">Escolha</option>
                                <option v-for="t in tipos" :key="t.id" :value="t.id">{{ t.label }}</option>
                            </select>
                        </div>
                        <div class="col-4 col-md-2 form-group text-right">
                            <button type="button" class="btn btn-sm btn-primary" :disabled="salvando" @click="salvar">
                                <i :class="salvando ? 'fa fa-spinner fa-pulse' : 'fa fa-check'"></i> {{ form.id ? 'Salvar' : 'Adicionar' }}
                            </button>
                        </div>
                    </div>
                    <a v-if="form.id" href="javascript://" class="small" @click="limpar">Cancelar edição</a>
                </fieldset>

                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Risco</th>
                                <th>Tipo</th>
                                <th>Status</th>
                                <th class="text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="r in riscos" :key="r.id" :class="{ 'text-muted': !r.ativo }">
                                <td>{{ r.label }}</td>
                                <td>{{ r.tipo || '—' }}</td>
                                <td><span class="badge" :class="r.ativo ? 'badge-success' : 'badge-secondary'">{{ r.ativo ? 'Ativo' : 'Inativo' }}</span></td>
                                <td class="text-right text-nowrap">
                                    <button type="button" class="btn btn-xs btn-outline-primary mr-1" title="Editar" @click="editar(r)"><i class="fa fa-pen"></i></button>
                                    <button type="button" class="btn btn-xs" :class="r.ativo ? 'btn-outline-warning' : 'btn-outline-success'" :title="r.ativo ? 'Desativar' : 'Ativar'" @click="alternar(r)">
                                        <i :class="r.ativo ? 'fa fa-pause' : 'fa fa-play'"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!riscos.length">
                                <td colspan="4" class="text-muted text-center">Nenhum risco cadastrado.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </template>
        <template #rodape>
            <button type="button" class="btn btn-sm btn-outline-secondary" @click="fechar">Fechar</button>
        </template>
    </modal>
</template>

<script>
import { defineComponent } from 'vue'
import axios from 'axios'

const BASE = () => `${URL_ADMIN}/saude-ocupacional/pgr/riscos`

export default defineComponent({
    name: 'RiscoCatalogoModal',

    props: {
        riscos: { type: Array, default: () => [] },
        tipos: { type: Array, default: () => [] }
    },

    emits: ['alterado'],

    data() {
        return { form: { id: null, label: '', risco_tipo: 0 }, salvando: false }
    },

    methods: {
        abrir() {
            this.limpar()
            this.$refs.modal && this.$refs.modal.abrirModal()
        },

        fechar() {
            this.$refs.modal && this.$refs.modal.fecharModal()
        },

        limpar() {
            this.form = { id: null, label: '', risco_tipo: 0 }
        },

        editar(risco) {
            this.form = { id: risco.id, label: risco.label, risco_tipo: risco.risco_tipo }
        },

        async enviar(id, dados) {
            const { data } = id ? await axios.put(`${BASE()}/${id}`, dados) : await axios.post(BASE(), dados)
            mostraSucesso('', data.mensagem)
            this.$emit('alterado')
        },

        async salvar() {
            this.salvando = true
            try {
                await this.enviar(this.form.id, { label: this.form.label, risco_tipo: this.form.risco_tipo })
                this.limpar()
            } catch (e) {
                // erro exibido pelo interceptor global do axios
            } finally {
                this.salvando = false
            }
        },

        async alternar(risco) {
            try {
                await this.enviar(risco.id, { label: risco.label, risco_tipo: risco.risco_tipo, ativo: !risco.ativo })
            } catch (e) {
                // erro exibido pelo interceptor global do axios
            }
        }
    }
})
</script>
